<?php

namespace App\Http\Controllers;

use App\Models\Recording;
use App\Models\RecordingParticipant;
use App\Models\RecordingDownloadRequest;
use App\Models\Meeting;
use App\Models\MeetingParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class RecordingController extends Controller
{
    /**
     * Upload meeting recording.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'meeting_id' => 'required|integer',
            'video'      => 'required|file',
        ]);

        try {
            $meeting = Meeting::withTrashed()->findOrFail($request->meeting_id);

            // Check if requester was a participant or host
            $isParticipant = MeetingParticipant::withTrashed()
                ->where('meeting_id', $meeting->id)
                ->where('user_id', Auth::id())
                ->exists();

            if (!$isParticipant && $meeting->created_by != Auth::id()) {
                return response()->json(['error' => 'Unauthorized to upload recordings for this meeting.'], 403);
            }

            if (!$request->hasFile('video')) {
                return response()->json(['error' => 'No video file provided'], 400);
            }

            $file = $request->file('video');
            $filename = 'recording_' . $meeting->room_id . '_' . time() . '.webm';
            
            // Store in secure non-public storage
            $path = $file->storeAs('recordings', $filename, 'local');

            $recording = Recording::create([
                'meeting_id' => $meeting->id,
                'user_id'    => Auth::id(),
                'file_path'  => $path,
                'file_name'  => $filename,
            ]);

            // Copy all participants who ever joined this meeting (plus the host)
            $participantUserIds = MeetingParticipant::withTrashed()
                ->where('meeting_id', $meeting->id)
                ->pluck('user_id')
                ->unique();
            
            $participantUserIds->push($meeting->created_by);
            $participantUserIds = $participantUserIds->unique();

            foreach ($participantUserIds as $userId) {
                RecordingParticipant::updateOrCreate([
                    'recording_id' => $recording->id,
                    'user_id'      => $userId,
                ]);
            }

            return response()->json(['success' => true, 'recording' => $recording]);

        } catch (\Exception $e) {
            Log::error('Recording upload failed: ' . $e->getMessage());
            return response()->json(['error' => 'Server error uploading recording: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show recordings dashboard.
     */
    public function index()
    {
        $userId = Auth::id();

        // 1. Get recordings of meetings HOSTED by this user
        $hostedMeetingIds = Meeting::withTrashed()
            ->where('created_by', $userId)
            ->pluck('id');

        $hostedRecordings = Recording::whereIn('meeting_id', $hostedMeetingIds)
            ->with(['meeting', 'user', 'downloadRequests.user'])
            ->latest()
            ->get();

        // Populate roster and download status for host's reference
        $hostedRecordings->each(function ($recording) use ($userId) {
            $recording->roster = RecordingParticipant::where('recording_id', $recording->id)
                ->where('user_id', '!=', $userId) // exclude the host
                ->with('user')
                ->get()
                ->map(function ($part) use ($recording) {
                    $req = $recording->downloadRequests->where('user_id', $part->user_id)->first();
                    $part->request_status = $req ? $req->status : 'none';
                    $part->request_id = $req ? $req->id : null;
                    return $part;
                });
        });

        // 2. Get recordings shared with user (meetings they attended but did not host)
        $sharedRecordingIds = RecordingParticipant::where('user_id', $userId)
            ->pluck('recording_id');

        $sharedRecordings = Recording::whereIn('id', $sharedRecordingIds)
            ->whereHas('meeting', function ($q) use ($userId) {
                $q->where('created_by', '!=', $userId);
            })
            ->with(['meeting', 'user'])
            ->latest()
            ->get();

        // Check current user's download request status for each shared recording
        $sharedRecordings->each(function ($recording) use ($userId) {
            $req = RecordingDownloadRequest::where('recording_id', $recording->id)
                ->where('user_id', $userId)
                ->first();
            $recording->my_request_status = $req ? $req->status : 'none';
        });

        return view('recordings.index', compact('hostedRecordings', 'sharedRecordings'));
    }

    /**
     * Request permission to download a recording.
     */
    public function requestDownload($id, Request $request)
    {
        $recording = Recording::findOrFail($id);

        // Check if user participated in this meeting
        $isParticipant = RecordingParticipant::where('recording_id', $recording->id)
            ->where('user_id', Auth::id())
            ->exists();

        if (!$isParticipant) {
            return $request->expectsJson()
                ? response()->json(['error' => 'Unauthorized access.'], 403)
                : back()->with('error', 'Unauthorized access.');
        }

        $req = RecordingDownloadRequest::updateOrCreate(
            [
                'recording_id' => $recording->id,
                'user_id'      => Auth::id(),
            ],
            [
                'status'       => 'pending'
            ]
        );

        return $request->expectsJson()
            ? response()->json(['success' => true, 'request' => $req])
            : back()->with('success', 'Download request submitted successfully.');
    }

    /**
     * Handle download permission action (Approve/Reject).
     */
    public function handleRequest($requestId, Request $request)
    {
        $downloadRequest = RecordingDownloadRequest::findOrFail($requestId);
        $recording = $downloadRequest->recording;
        $meeting = $recording->meeting;

        // Verify the current user is the host
        if ($meeting->created_by != Auth::id()) {
            return $request->expectsJson()
                ? response()->json(['error' => 'Unauthorized action.'], 403)
                : back()->with('error', 'Unauthorized action.');
        }

        $action = $request->input('action');
        $status = $action === 'approve' ? 'approved' : 'rejected';

        $downloadRequest->update(['status' => $status]);

        return $request->expectsJson()
            ? response()->json(['success' => true, 'status' => $status, 'request' => $downloadRequest])
            : back()->with('success', 'Request has been ' . $status . '.');
    }

    /**
     * Sync download request statuses in real-time.
     */
    public function syncRequests()
    {
        $userId = Auth::id();

        // 1. Host side: Requests on recordings hosted by current user
        $hostedMeetingIds = Meeting::withTrashed()
            ->where('created_by', $userId)
            ->pluck('id');

        $hostedRecordingIds = Recording::whereIn('meeting_id', $hostedMeetingIds)->pluck('id');

        $hostRequests = RecordingDownloadRequest::whereIn('recording_id', $hostedRecordingIds)
            ->get()
            ->map(function ($req) {
                return [
                    'id'           => $req->id,
                    'recording_id' => $req->recording_id,
                    'user_id'      => $req->user_id,
                    'status'       => $req->status,
                ];
            });

        // 2. Participant side: All download requests initiated by current user
        $myRequests = RecordingDownloadRequest::where('user_id', $userId)
            ->get()
            ->map(function ($req) {
                return [
                    'id'           => $req->id,
                    'recording_id' => $req->recording_id,
                    'status'       => $req->status,
                ];
            });

        return response()->json([
            'host_requests' => $hostRequests,
            'my_requests'   => $myRequests,
        ]);
    }

    /**
     * Secure stream/play of recording.
     */
    public function play($id)
    {
        $recording = Recording::findOrFail($id);

        $isHost = $recording->meeting->created_by == Auth::id();

        if (!$isHost) {
            abort(403, 'Only the host is authorized to play this recording.');
        }

        if (!Storage::disk('local')->exists($recording->file_path)) {
            abort(404, 'Video file not found on server.');
        }

        $path = Storage::disk('local')->path($recording->file_path);

        return response()->file($path, [
            'Content-Type'        => 'video/webm',
            'Content-Disposition' => 'inline',
        ]);
    }

    /**
     * Secure download of recording.
     */
    public function download($id)
    {
        $recording = Recording::findOrFail($id);

        $isHost = $recording->meeting->created_by == Auth::id();
        
        if (!$isHost) {
            $isApproved = RecordingDownloadRequest::where('recording_id', $recording->id)
                ->where('user_id', Auth::id())
                ->where('status', 'approved')
                ->exists();

            if (!$isApproved) {
                abort(403, 'You do not have permission to download this recording.');
            }
        }

        if (!Storage::disk('local')->exists($recording->file_path)) {
            abort(404, 'Recording file not found on server.');
        }

        $path = Storage::disk('local')->path($recording->file_path);

        return response()->download($path, 'meeting_recording_' . $recording->id . '.webm');
    }

    /**
     * Delete recording.
     */
    public function destroy($id)
    {
        $recording = Recording::findOrFail($id);

        if ($recording->meeting->created_by != Auth::id()) {
            return back()->with('error', 'Unauthorized action.');
        }

        if (Storage::disk('local')->exists($recording->file_path)) {
            Storage::disk('local')->delete($recording->file_path);
        }

        $recording->delete();

        return back()->with('success', 'Recording deleted successfully.');
    }
}
