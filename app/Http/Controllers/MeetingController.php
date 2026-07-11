<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\MeetingParticipant;
use App\Models\MeetingMessage;
use App\Models\Signal;
use App\Events\ParticipantJoined;
use App\Events\MeetingEnded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Events\HandRaised;

class MeetingController extends Controller
{
    public function create()
    {
        try {
            $roomId = strtoupper(Str::random(6));

            $meeting = Meeting::create([
                'room_id'      => $roomId,
                'created_by'   => Auth::id(),
                'meeting_name' => 'Meeting ' . $roomId,
            ]);

            $peerId = 'user-' . Auth::id() . '-' . time();

            MeetingParticipant::create([
                'meeting_id' => $meeting->id,
                'user_id' => Auth::id(),
                'peer_id' => $peerId,
            ]);

            return redirect()->route('meeting.join', $roomId);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create meeting: ' . $e->getMessage());
        }
    }

    public function join($roomId)
    {
        $meeting = Meeting::where('room_id', $roomId)->firstOrFail();

        $participant = MeetingParticipant::where('meeting_id', $meeting->id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$participant) {
            $peerId = 'user-' . Auth::id() . '-' . time();

            MeetingParticipant::create([
                'meeting_id' => $meeting->id,
                'user_id' => Auth::id(),
                'peer_id' => $peerId,
            ]);
        }

        return view('meeting', compact('meeting'));
    }

    private function joinMeeting($meetingId, $peerId)
    {
        $participant = MeetingParticipant::updateOrCreate(
            [
                'meeting_id' => $meetingId,
                'user_id'    => Auth::id(),
            ],
            [
                'peer_id' => $peerId,
            ]
        );

        try {
            // ✅ REMOVED ->toOthers() - Now broadcasts to ALL including sender
            broadcast(new ParticipantJoined($participant));
        } catch (\Exception $e) {
            Log::warn('Signaling broadcast deferred: ' . $e->getMessage());
        }

        return $participant;
    }

    public function getParticipants($meetingId)
    {
        $meeting = Meeting::findOrFail($meetingId);

        $participants = MeetingParticipant::where('meeting_id', $meetingId)
            ->with('user')
            ->get()
            ->map(function ($p) use ($meeting) {
                return [
                    'user_id'        => $p->user_id,
                    'user_name'      => $p->user ? $p->user->name : 'Unknown User',
                    'peer_id'        => $p->peer_id ?: ($p->user ? $p->user->peer_id : null),
                    'is_audio_muted' => (bool) ($p->is_audio_muted ?? false),
                    'is_video_off'   => (bool) ($p->is_video_off ?? false),
                    'is_admin'       => (bool) ($p->is_admin ?? false),
                    'is_host'        => (int) $p->user_id === (int) $meeting->created_by,
                    'hand_raised'    => (bool) ($p->hand_raised ?? false),
                ];
            });

        return response()->json($participants);
    }

    public function leave(Request $request)
    {
        MeetingParticipant::where('user_id', Auth::id())
            ->where('meeting_id', $request->meeting_id)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function endMeeting($meetingId)
    {
        $meeting = Meeting::findOrFail($meetingId);

        if ($meeting->created_by != Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            // ✅ REMOVED ->toOthers() - Now broadcasts to ALL including sender
            broadcast(new MeetingEnded($meetingId, 'Meeting ended by host'));
        } catch (\Exception $e) {
            Log::error('Meeting termination broadcast breakdown: ' . $e->getMessage());
        }

        MeetingParticipant::where('meeting_id', $meetingId)->delete();
        $meeting->delete();

        return response()->json(['success' => true]);
    }

    // ============================================================
    // SIGNALING PROCESSING HUB
    // ============================================================
    public function sendSignal(Request $request)
    {
        try {
            Signal::create([
                'meeting_id'   => $request->meeting_id,
                'from_peer_id' => $request->peer_id,
                'to_peer_id'   => $request->target_peer_id,
                'signal_data'  => json_encode($request->signal),
                'processed'    => false,
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getSignals($meetingId, $peerId)
    {
        try {
            $signals = Signal::where('meeting_id', $meetingId)
                ->where('to_peer_id', $peerId)
                ->where('processed', false)
                ->get();

            if ($signals->isNotEmpty()) {
                Signal::whereIn('id', $signals->pluck('id'))->update(['processed' => true]);
            }

            return response()->json($signals->map(function ($signal) {
                return [
                    'from_peer_id' => $signal->from_peer_id,
                    'signal_data'  => json_decode($signal->signal_data, true),
                ];
            }));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ============================================================
    // HOST MODERATION ENGINE
    // ============================================================
    public function toggleAudio($meetingId, $userId, Request $request)
    {
        $meeting = Meeting::findOrFail($meetingId);
        $actor = MeetingParticipant::where('meeting_id', $meetingId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$actor || (!($actor->is_admin ?? false) && $meeting->created_by != Auth::id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $participant = MeetingParticipant::where('meeting_id', $meetingId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $participant->is_audio_muted = filter_var($request->is_audio_muted, FILTER_VALIDATE_BOOLEAN);
        $participant->save();

        return response()->json(['success' => true]);
    }

    public function toggleVideo($meetingId, $userId, Request $request)
    {
        $meeting = Meeting::findOrFail($meetingId);
        $actor = MeetingParticipant::where('meeting_id', $meetingId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$actor || (!($actor->is_admin ?? false) && $meeting->created_by != Auth::id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $participant = MeetingParticipant::where('meeting_id', $meetingId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $participant->is_video_off = filter_var($request->is_video_off, FILTER_VALIDATE_BOOLEAN);
        $participant->save();

        return response()->json(['success' => true]);
    }

    public function kickParticipant($meetingId, $userId)
    {
        $meeting = Meeting::findOrFail($meetingId);
        $actor = MeetingParticipant::where('meeting_id', $meetingId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$actor || (!($actor->is_admin ?? false) && $meeting->created_by != Auth::id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        MeetingParticipant::where('meeting_id', $meetingId)
            ->where('user_id', $userId)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function makeAdmin($meetingId, $userId)
    {
        $meeting = Meeting::findOrFail($meetingId);
        $actor = MeetingParticipant::where('meeting_id', $meetingId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$actor || $meeting->created_by != Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $participant = MeetingParticipant::where('meeting_id', $meetingId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $participant->is_admin = true;
        $participant->save();

        return response()->json(['success' => true]);
    }

    // ============================================================
    // LIVE STREAM METADATA SYNC
    // ============================================================
    public function updateStatus($meetingId, Request $request)
    {
        $participant = MeetingParticipant::where('meeting_id', $meetingId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($request->has('peer_id')) {
            $participant->peer_id = $request->peer_id;
        }

        if ($request->has('is_audio_muted')) {
            $participant->is_audio_muted = filter_var($request->is_audio_muted, FILTER_VALIDATE_BOOLEAN);
        }

        if ($request->has('is_video_off')) {
            $participant->is_video_off = filter_var($request->is_video_off, FILTER_VALIDATE_BOOLEAN);
        }

        $participant->save();

        return response()->json(['success' => true]);
    }

    public function raiseHand(Request $request)
    {
        $request->validate([
            'meetingId' => 'required|integer',
            'userId' => 'required|integer',
            'peerId' => 'nullable|string',
            'raised' => 'required',
        ]);

        $raised = filter_var($request->input('raised'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($raised === null) {
            return response()->json(['error' => 'Invalid raised value'], 422);
        }

        $participant = MeetingParticipant::where('meeting_id', $request->meetingId)
            ->where('user_id', $request->userId)
            ->first();

        if ($participant) {
            $participant->hand_raised = $raised;
            $participant->save();
        }

        $peerId = $request->peerId ?: ($participant?->peer_id);

        broadcast(new HandRaised(
            $request->meetingId,
            $request->userId,
            $peerId,
            $raised
        ));

        return response()->json([
            'success' => true,
            'raised' => $raised,
            'broadcasted' => true,
        ]);
    }

    public function broadcastChat(Request $request)
    {
        $request->validate([
            'meeting_id' => 'required|integer',
            'message' => 'required|string|max:1000',
            'user_id' => 'required|integer',
            'sender_name' => 'nullable|string',
        ]);

        $isParticipant = MeetingParticipant::where('meeting_id', $request->meeting_id)
            ->where('user_id', Auth::id())
            ->exists();

        if (!$isParticipant) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $senderName = $request->sender_name ?? Auth::user()->name ?? 'User';

        $storedMessage = MeetingMessage::create([
            'meeting_id' => $request->meeting_id,
            'user_id' => $request->user_id,
            'sender_name' => $senderName,
            'message' => $request->message,
        ]);

        broadcast(new \App\Events\MeetingChatMessage(
            $request->meeting_id,
            $request->user_id,
            $senderName,
            $request->message,
            $storedMessage->id
        ));

        return response()->json([
            'success' => true,
            'message_id' => $storedMessage->id,
        ]);
    }

    public function getChatMessages($meetingId, Request $request)
    {
        $isParticipant = MeetingParticipant::where('meeting_id', $meetingId)
            ->where('user_id', Auth::id())
            ->exists();

        if (!$isParticipant) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = MeetingMessage::where('meeting_id', $meetingId)
            ->orderBy('id');

        if ($request->filled('after_id')) {
            $query->where('id', '>', (int) $request->after_id);
        }

        $messages = $query->get()->map(function ($message) {
            return [
                'id' => $message->id,
                'user_id' => $message->user_id,
                'sender_name' => $message->sender_name,
                'message' => $message->message,
                'created_at' => $message->created_at?->toIso8601String(),
            ];
        });

        return response()->json($messages);
    }

    public function syncMeeting($meetingId, $peerId, Request $request)
    {
        $userId = Auth::id();
        $isParticipant = MeetingParticipant::where('meeting_id', $meetingId)
            ->where('user_id', $userId)
            ->exists();

        if (!$isParticipant) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // 1. Get signals for peer
        $signals = Signal::where('meeting_id', $meetingId)
            ->where('to_peer_id', $peerId)
            ->where('processed', false)
            ->get();

        if ($signals->isNotEmpty()) {
            Signal::whereIn('id', $signals->pluck('id'))->update(['processed' => true]);
        }

        $formattedSignals = $signals->map(function ($signal) {
            return [
                'from_peer_id' => $signal->from_peer_id,
                'signal_data'  => json_decode($signal->signal_data, true),
            ];
        });

        // 2. Get participants
        $meeting = Meeting::findOrFail($meetingId);
        $participants = MeetingParticipant::where('meeting_id', $meetingId)
            ->with('user')
            ->get()
            ->map(function ($p) use ($meeting) {
                return [
                    'user_id'        => $p->user_id,
                    'user_name'      => $p->user ? $p->user->name : 'Unknown User',
                    'peer_id'        => $p->peer_id ?: ($p->user ? $p->user->peer_id : null),
                    'is_audio_muted' => (bool) ($p->is_audio_muted ?? false),
                    'is_video_off'   => (bool) ($p->is_video_off ?? false),
                    'is_admin'       => (bool) ($p->is_admin ?? false),
                    'is_host'        => (int) $p->user_id === (int) $meeting->created_by,
                    'hand_raised'    => (bool) ($p->hand_raised ?? false),
                ];
            });

        // 3. Get chat messages
        $chatQuery = MeetingMessage::where('meeting_id', $meetingId)
            ->orderBy('id');

        if ($request->filled('after_chat_id')) {
            $chatQuery->where('id', '>', (int) $request->after_chat_id);
        }

        $messages = $chatQuery->get()->map(function ($message) {
            return [
                'id' => $message->id,
                'user_id' => $message->user_id,
                'sender_name' => $message->sender_name,
                'message' => $message->message,
                'created_at' => $message->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'signals'      => $formattedSignals,
            'participants' => $participants,
            'messages'     => $messages,
        ]);
    }
}
