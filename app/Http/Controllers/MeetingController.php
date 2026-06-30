<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\MeetingParticipant;
use App\Models\Signal;
use App\Events\ParticipantJoined;
use App\Events\MeetingEnded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MeetingController extends Controller
{
    public function create()
    {
        $roomId = strtoupper(Str::random(6));

        $meeting = Meeting::create([
            'room_id'      => $roomId,
            'created_by'   => Auth::id(),
            'meeting_name' => 'Meeting ' . $roomId,
        ]);

        $this->joinMeeting($meeting->id, Auth::user()->peer_id);

        return redirect()->route('meeting.join', $roomId);
    }

    public function join($roomId)
    {
        $meeting = Meeting::where('room_id', $roomId)->firstOrFail();

        $participant = MeetingParticipant::where('meeting_id', $meeting->id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$participant) {
            $this->joinMeeting($meeting->id, Auth::user()->peer_id);
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

        broadcast(new ParticipantJoined($participant))->toOthers();

        return $participant;
    }

    public function getParticipants($meetingId)
    {
        $participants = MeetingParticipant::where('meeting_id', $meetingId)
            ->with('user')
            ->get()
            ->map(function ($p) {
                return [
                    'user_id' => $p->user_id,
                    'user_name' => $p->user ? $p->user->name : 'Unknown',
                    'peer_id' => $p->peer_id ?: ($p->user ? $p->user->peer_id : null),
                    'is_audio_muted' => $p->is_audio_muted ?? false,
                    'is_video_off' => $p->is_video_off ?? false,
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
            broadcast(new MeetingEnded($meetingId, 'Meeting ended by host'));
        } catch (\Exception $e) {
            Log::error('Broadcast error: ' . $e->getMessage());
        }

        MeetingParticipant::where('meeting_id', $meetingId)->delete();
        $meeting->delete();

        return response()->json(['success' => true]);
    }

    // ============================================================
    // SIGNAL METHODS
    // ============================================================
    public function sendSignal(Request $request)
    {
        try {
            $signal = Signal::create([
                'meeting_id' => $request->meeting_id,
                'from_peer_id' => $request->peer_id,
                'to_peer_id' => $request->target_peer_id,
                'signal_data' => json_encode($request->signal),
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

            // Mark as processed
            Signal::whereIn('id', $signals->pluck('id'))->update(['processed' => true]);

            return response()->json($signals->map(function ($signal) {
                return [
                    'from_peer_id' => $signal->from_peer_id,
                    'signal_data' => json_decode($signal->signal_data, true),
                ];
            }));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function toggleAudio($meetingId, $userId, Request $request)
    {
        $meeting = Meeting::findOrFail($meetingId);
        if ($meeting->created_by != Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $participant = MeetingParticipant::where('meeting_id', $meetingId)
            ->where('user_id', $userId)
            ->first();
        if (!$participant) {
            return response()->json(['error' => 'Participant not found'], 404);
        }

        $participant->is_audio_muted = $request->is_audio_muted;
        $participant->save();

        return response()->json(['success' => true]);
    }

    public function toggleVideo($meetingId, $userId, Request $request)
    {
        $meeting = Meeting::findOrFail($meetingId);
        if ($meeting->created_by != Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $participant = MeetingParticipant::where('meeting_id', $meetingId)
            ->where('user_id', $userId)
            ->first();
        if (!$participant) {
            return response()->json(['error' => 'Participant not found'], 404);
        }

        $participant->is_video_off = $request->is_video_off;
        $participant->save();

        return response()->json(['success' => true]);
    }

    public function kickParticipant($meetingId, $userId)
    {
        $meeting = Meeting::findOrFail($meetingId);
        if ($meeting->created_by != Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        MeetingParticipant::where('meeting_id', $meetingId)
            ->where('user_id', $userId)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function updateStatus($meetingId, Request $request)
    {
        $participant = MeetingParticipant::where('meeting_id', $meetingId)
            ->where('user_id', Auth::id())
            ->first();
        if (!$participant) {
            return response()->json(['error' => 'Participant not found'], 404);
        }

        $participant->is_audio_muted = $request->is_audio_muted;
        $participant->is_video_off = $request->is_video_off;
        $participant->save();

        return response()->json(['success' => true]);
    }
}
