<x-app-layout>
    <div class="min-h-screen bg-slate-950 text-slate-100 py-10 px-4 md:px-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between gap-4 mb-10 p-6 bg-slate-900 border-2 border-indigo-800 rounded-2xl shadow-xl">
                <div class="min-w-0">
                    <h1 class="text-xl font-bold text-white tracking-tight">Recordings</h1>
                </div>
                <a href="{{ route('dashboard') }}" class="h-10 px-4 shrink-0 rounded-xl bg-indigo-600 hover:bg-indigo-500 border-2 border-indigo-800 text-white text-sm font-semibold transition flex items-center justify-center shadow-md active:scale-95 ml-auto" aria-label="Back to dashboard">
                    Back
                </a>
            </div>

            <!-- Session Messages -->
            @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm flex items-center justify-between gap-2 alert-banner shadow-sm">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ session('success') }}
                </span>
                <button type="button" onclick="this.closest('.alert-banner').remove()" class="text-emerald-400 hover:text-emerald-200 transition font-bold text-lg leading-none p-1">&times;</button>
            </div>
            @endif
            @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm flex items-center justify-between gap-2 alert-banner shadow-sm">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    {{ session('error') }}
                </span>
                <button type="button" onclick="this.closest('.alert-banner').remove()" class="text-rose-400 hover:text-rose-200 transition font-bold text-lg leading-none p-1">&times;</button>
            </div>
            @endif

            <!-- Grid Layout -->
            <div class="flex flex-col gap-8">

                <!-- 1. Hosted Recordings -->
                <div class="space-y-6 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                    <h2 class="text-xl font-bold text-indigo-400 flex items-center gap-2">
                        As Host
                    </h2>

                    @if($hostedRecordings->isEmpty())
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-10 text-center text-slate-500 shadow-lg">
                        <p class="text-sm">No records</p>
                    </div>
                    @else
                    @foreach($hostedRecordings as $rec)
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl hover:border-slate-700/85 transition-all duration-300 flex flex-col gap-4">
                        <div>
                            <div class="flex justify-between items-start gap-2 mb-2">
                                <h3 class="font-bold text-white text-base truncate">{{ str_replace('Meeting ', '', $rec->meeting->meeting_name ?? '') }} | {{ $rec->created_at->format('M d, Y') }} | {{ $rec->created_at->format('h:i A') }}</h3>
                                <span class="text-base font-bold text-slate-300 shrink-0">{{ $rec->duration }}</span>
                            </div>
                            <p class="text-xs text-slate-400">Recorded by: <span class="text-slate-350 font-medium">{{ $rec->user->name ?? 'Unknown' }}</span></p>
                        </div>

                        <!-- Host Actions -->
                        <div class="flex flex-wrap items-center gap-2 pt-3">
                            <button data-url="{{ route('recordings.play', $rec->id) }}" data-title="{{ $rec->meeting->meeting_name ?? 'Recording' }}" onclick="playVideo(this.dataset.url, this.dataset.title)"
                                class="h-9 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 border-2 border-indigo-800 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-md active:scale-95">
                                Play
                            </button>
                            <a href="{{ route('recordings.download', $rec->id) }}"
                                class="h-9 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 border-2 border-indigo-800 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-md active:scale-95">
                                Download
                            </a>
                            <form action="{{ route('recordings.destroy', $rec->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this recording?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="h-9 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 border-2 border-indigo-800 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-md active:scale-95">
                                    Delete
                                </button>
                            </form>
                        </div>

                        <!-- Roster Permission Area -->
                        @if($rec->roster && $rec->roster->isNotEmpty())
                        <div class="bg-slate-950/40 border border-slate-800 rounded-xl p-4 mt-2">
                            <h4 class="text-xs font-bold text-slate-450 uppercase tracking-wider mb-3">Participant Download Status</h4>
                            <div class="space-y-3">
                                @foreach($rec->roster as $part)
                                <div class="flex items-center justify-between gap-4 text-xs host-request-row" data-recording-id="{{ $rec->id }}" data-user-id="{{ $part->user_id }}" data-user-name="{{ addslashes($part->user->name ?? 'Unknown User') }}">
                                    <span class="text-slate-300 font-medium truncate">{{ $part->user->name ?? 'Unknown User' }}</span>

                                    <div class="host-status-container flex items-center gap-2">
                                        @if($part->request_status === 'pending')
                                        <span class="text-amber-400 bg-amber-500/10 border-2 border-amber-500 px-2 py-0.5 rounded-xl text-[10px] font-medium">Pending Request</span>
                                        <button type="button" data-request-id="{{ $part->request_id }}" onclick="actionDownloadRequest(this.dataset.requestId, 'approve', this)" class="h-8 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 border-2 border-indigo-800 shadow-md text-white text-[10px] font-bold transition active:scale-95">Approve</button>
                                        <button type="button" data-request-id="{{ $part->request_id }}" onclick="actionDownloadRequest(this.dataset.requestId, 'reject', this)" class="h-8 px-3 rounded-xl bg-slate-950 border border-slate-800 text-slate-200 text-[10px] font-bold hover:bg-slate-800 transition ml-1 active:scale-95">Reject</button>
                                        @elseif($part->request_status === 'approved')
                                        <span class="text-emerald-400 bg-emerald-500/10 border-2 border-emerald-500 px-2 py-0.5 rounded-xl text-[10px] font-medium">✓</span>
                                        <button type="button" data-request-id="{{ $part->request_id }}" onclick="actionDownloadRequest(this.dataset.requestId, 'reject', this)" class="h-8 w-8 rounded-xl bg-indigo-600 hover:bg-indigo-500 border-2 border-indigo-800 text-white text-xs font-bold transition ml-2 shadow-md active:scale-95 flex items-center justify-center">✕</button>
                                        @elseif($part->request_status === 'rejected')
                                        <span class="text-rose-400 bg-rose-500/10 border-2 border-rose-500 px-2 py-0.5 rounded-xl text-[10px] font-medium">✕</span>
                                        <button type="button" data-request-id="{{ $part->request_id }}" onclick="actionDownloadRequest(this.dataset.requestId, 'approve', this)" class="h-8 w-8 rounded-xl bg-indigo-600 hover:bg-indigo-500 border-2 border-indigo-800 text-white text-xs font-bold transition ml-2 shadow-md active:scale-95 flex items-center justify-center">✓</button>
                                        @else
                                        <span class="text-slate-500 text-[10px]">No download request</span>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    @endforeach
                    @endif
                </div>

                <!-- Thin white separator line -->
                <div class="border-t border-white/20 my-4"></div>

                <!-- 2. Shared Recordings -->
                <div class="space-y-6 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                    <h2 class="text-xl font-bold text-indigo-400 flex items-center gap-2">
                        As Member
                    </h2>

                    @if($sharedRecordings->isEmpty())
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-10 text-center text-slate-500 shadow-lg">
                        <p class="text-sm">No records</p>
                    </div>
                    @else
                    @foreach($sharedRecordings as $rec)
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl hover:border-slate-700/85 transition-all duration-300 flex flex-col gap-4">
                        <div>
                            <div class="flex justify-between items-start gap-2 mb-2">
                                <h3 class="font-bold text-white text-base truncate">{{ str_replace('Meeting ', '', $rec->meeting->meeting_name ?? '') }} | {{ $rec->created_at->format('M d, Y') }} | {{ $rec->created_at->format('h:i A') }}</h3>
                                <span class="text-base font-bold text-slate-300 shrink-0">{{ $rec->duration }}</span>
                            </div>
                            <p class="text-xs text-slate-400">Host: <span class="text-slate-350 font-medium">{{ $rec->meeting->creator->name ?? 'Unknown' }}</span></p>
                            <p class="text-xs text-slate-400 mt-1">Recorded by: <span class="text-slate-350 font-medium">{{ $rec->user->name ?? 'Unknown' }}</span></p>
                        </div>

                        <!-- Participant Actions -->
                        <div class="flex flex-wrap items-center justify-between gap-2 pt-3">
                            <div></div> <!-- Left space holder as Play button is removed -->

                            <div class="participant-actions-container font-semibold" data-recording-id="{{ $rec->id }}" data-download-url="{{ route('recordings.download', $rec->id) }}">
                                @if($rec->my_request_status === 'approved')
                                <a href="{{ route('recordings.download', $rec->id) }}"
                                    class="h-9 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 border-2 border-indigo-800 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-md active:scale-95">
                                    Download
                                </a>
                                @elseif($rec->my_request_status === 'pending')
                                <button disabled class="h-9 px-4 rounded-xl bg-slate-950 border border-slate-800 text-slate-400 text-xs font-bold transition cursor-not-allowed">
                                    Download Pending
                                </button>
                                @elseif($rec->my_request_status === 'rejected')
                                <button disabled class="h-9 px-4 rounded-xl bg-slate-950 border border-slate-800 text-slate-400 text-xs font-bold transition cursor-not-allowed">
                                    Download Rejected
                                </button>
                                @else
                                <button type="button" data-recording-id="{{ $rec->id }}" onclick="submitRequestDownload(this.dataset.recordingId, this)"
                                    class="h-9 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 border-2 border-indigo-800 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-md active:scale-95">
                                    Request Download
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>

            </div>
        </div>
    </div>

    <!-- Video Player Modal -->
    <div id="videoModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 backdrop-blur-sm hidden">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-4xl w-full mx-4 overflow-hidden shadow-2xl relative transition-all duration-300">

            <!-- Modal Header -->
            <div class="px-6 py-4 bg-slate-900 border-b border-slate-800 flex justify-between items-center">
                <h3 id="videoModalTitle" class="font-bold text-white text-base truncate">Play Recording</h3>
                <button onclick="closeVideo()" class="h-10 w-10 rounded-xl bg-indigo-600 hover:bg-indigo-500 border-2 border-indigo-800 text-white transition flex items-center justify-center shadow-md active:scale-95">Close</button>
            </div>

            <!-- Video Content -->
            <div class="aspect-video w-full bg-black flex items-center justify-center">
                <video id="modalVideoPlayer" controls class="w-full h-full object-contain" src=""></video>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-slate-900 border-t border-slate-800 flex justify-end">
                <button onclick="closeVideo()" class="h-10 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 border-2 border-indigo-800 text-white text-sm font-semibold transition shadow-md active:scale-95">Close</button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        function playVideo(url, title) {
            const modal = document.getElementById('videoModal');
            const video = document.getElementById('modalVideoPlayer');
            const titleEl = document.getElementById('videoModalTitle');

            if (modal && video) {
                titleEl.textContent = title;
                video.src = url;
                modal.classList.remove('hidden');
                video.play().catch(e => console.log('Autoplay blocked: ', e));
            }
        }

        function closeVideo() {
            const modal = document.getElementById('videoModal');
            const video = document.getElementById('modalVideoPlayer');

            if (modal && video) {
                video.pause();
                video.src = '';
                modal.classList.add('hidden');
            }
        }

        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeVideo();
            }
        });

        // Periodically sync download request statuses
        function startStatusPolling() {
            syncRequestStatuses();
            setInterval(syncRequestStatuses, 3000);
        }

        function syncRequestStatuses() {
            const syncRequestsUrl = "{{ route('recordings.sync-requests') }}";

            fetch(syncRequestsUrl, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(res => {
                    if (!res.ok) throw new Error('Sync failed');
                    return res.json();
                })
                .then(data => {
                    // 1. Update Host-side rows
                    const hostRows = document.querySelectorAll('.host-request-row');
                    hostRows.forEach(row => {
                        const recId = parseInt(row.getAttribute('data-recording-id'));
                        const userId = parseInt(row.getAttribute('data-user-id'));
                        const statusContainer = row.querySelector('.host-status-container');

                        if (statusContainer) {
                            // Find matching request in host_requests
                            const match = data.host_requests.find(r => r.recording_id === recId && r.user_id === userId);
                            const currentStatus = match ? match.status : 'none';
                            const reqId = match ? match.id : null;

                            // Render correct HTML state
                            let html = '';
                            if (currentStatus === 'pending') {
                                html = `
                                <span class="text-amber-400 bg-amber-500/10 border-2 border-amber-500 px-2 py-0.5 rounded-xl text-[10px] font-medium">Pending Request</span>
                                <button type="button" onclick="actionDownloadRequest(${reqId}, 'approve', this)" class="h-8 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 border-2 border-indigo-800 shadow-md text-white text-[10px] font-bold transition active:scale-95">Approve</button>
                                <button type="button" onclick="actionDownloadRequest(${reqId}, 'reject', this)" class="h-8 px-3 rounded-xl bg-slate-950 border border-slate-800 text-slate-200 text-[10px] font-bold hover:bg-slate-800 transition ml-1 active:scale-95">Reject</button>
                            `;
                            } else if (currentStatus === 'approved') {
                                html = `
                                <span class="text-emerald-400 bg-emerald-500/10 border-2 border-emerald-500 px-2 py-0.5 rounded-xl text-[10px] font-medium">Approved ✅</span>
                                <button type="button" onclick="actionDownloadRequest(${reqId}, 'reject', this)" class="h-8 w-8 rounded-xl bg-indigo-600 hover:bg-indigo-500 border-2 border-indigo-800 text-white text-[10px] font-bold transition ml-2 shadow-md active:scale-95 flex items-center justify-center">✕</button>
                            `;
                            } else if (currentStatus === 'rejected') {
                                html = `
                                <span class="text-rose-400 bg-rose-500/10 border-2 border-rose-500 px-2 py-0.5 rounded-xl text-[10px] font-medium">Rejected ❌</span>
                                <button type="button" onclick="actionDownloadRequest(${reqId}, 'approve', this)" class="h-8 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 border-2 border-indigo-800 text-white text-[10px] font-bold transition ml-2 shadow-md active:scale-95">✓</button>
                            `;
                            } else {
                                html = `<span class="text-slate-500 text-[10px]">No download request</span>`;
                            }

                            if (statusContainer.innerHTML.trim() !== html.trim()) {
                                statusContainer.innerHTML = html;
                            }
                        }
                    });

                    // 2. Update Participant-side buttons
                    const participantContainers = document.querySelectorAll('.participant-actions-container');
                    participantContainers.forEach(container => {
                        const recId = parseInt(container.getAttribute('data-recording-id'));
                        const downloadUrl = container.getAttribute('data-download-url');

                        const match = data.my_requests.find(r => r.recording_id === recId);
                        const currentStatus = match ? match.status : 'none';

                        let html = '';
                        if (currentStatus === 'approved') {
                            html = `
                            <a href="${downloadUrl}"
                                class="h-9 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 border-2 border-indigo-800 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-md active:scale-95">
                                Download
                            </a>
                        `;
                        } else if (currentStatus === 'pending') {
                            html = `
                            <button disabled class="h-9 px-4 rounded-xl bg-slate-950 border border-slate-800 text-slate-400 text-xs font-bold transition cursor-not-allowed">
                                Download Pending ⏳
                            </button>
                        `;
                        } else if (currentStatus === 'rejected') {
                            html = `
                            <button disabled class="h-9 px-4 rounded-xl bg-slate-950 border border-slate-800 text-slate-400 text-xs font-bold transition cursor-not-allowed">
                             Rejected ❌
                            </button>
                        `;
                        } else {
                            html = `
                            <button type="button" onclick="submitRequestDownload(${recId}, this)"
                                class="h-9 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 border-2 border-indigo-800 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-md active:scale-95">
                                Request Download
                            </button>
                        `;
                        }

                        if (container.innerHTML.trim() !== html.trim()) {
                            container.innerHTML = html;
                        }
                    });
                })
                .catch(err => console.warn('Status sync failed:', err));
        }

        // AJAX approve/reject action for host
        function actionDownloadRequest(requestId, action, btn) {
            btn.disabled = true;
            const originalText = btn.textContent;
            btn.textContent = '...';

            fetch(`/recordings/download-requests/${requestId}/action`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        action: action
                    })
                })
                .then(res => {
                    if (!res.ok) throw new Error('Action failed');
                    return res.json();
                })
                .then(data => {
                    // Instantly sync layout
                    syncRequestStatuses();
                })
                .catch(err => {
                    console.error(err);
                    alert('Failed to process download request.');
                    btn.disabled = false;
                    btn.textContent = originalText;
                });
        }

        // AJAX submit download request for participant
        function submitRequestDownload(recId, btn) {
            btn.disabled = true;
            const originalText = btn.textContent;
            btn.textContent = '⏳ ...';

            fetch(`/recordings/${recId}/request-download`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => {
                    if (!res.ok) throw new Error('Request failed');
                    return res.json();
                })
                .then(data => {
                    // Instantly sync layout
                    syncRequestStatuses();
                })
                .catch(err => {
                    console.error(err);
                    alert('Failed to submit download request.');
                    btn.disabled = false;
                    btn.textContent = originalText;
                });
        }

        // Start status sync
        document.addEventListener('DOMContentLoaded', startStatusPolling);
    </script>
</x-app-layout>