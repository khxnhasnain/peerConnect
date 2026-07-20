<x-app-layout>
    <div class="min-h-screen bg-slate-950 text-slate-100 flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-2xl text-center relative overflow-hidden">
            <!-- Pulsing design element -->
            <div class="absolute -top-10 -left-10 w-40 h-40 bg-indigo-600/10 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-indigo-600/10 rounded-full blur-3xl animate-pulse"></div>

            <div class="relative z-10">
                <div class="w-12 h-12 rounded-full bg-indigo-500/10 flex items-center justify-center border-2 border-slate-950 mx-auto mb-6" style="width: 48px; height: 48px; min-width: 48px; min-height: 48px;">
                    <svg class="w-6 h-6 text-indigo-400 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px; min-width: 24px; min-height: 24px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <h1 class="text-2xl font-bold text-white mb-2 truncate">
                    {{ $meeting->meeting_name ?? 'Scheduled Meeting' }}
                </h1>
                


                <!-- Countdown timer UI -->
                <div class="mb-6">
                    <span id="countdownTimer" class="text-2xl font-extrabold font-mono tracking-wider text-indigo-400">00:00:00</span>
                </div>

                <p class="text-sm text-slate-400 mb-4 max-w-sm mx-auto leading-relaxed">
                    Preparing your meeting... You'll be redirected automatically.
                </p>

                <div class="flex w-full gap-3 mt-6">
                    <a href="{{ route('dashboard') }}" class="flex-1 h-11 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 border-2 border-indigo-800 text-white text-sm font-semibold flex items-center justify-center transition active:scale-95 shadow-md">
                        Back to Dashboard
                    </a>

                    @if(Auth::id() == $meeting->created_by)
                        <!-- Host Controls -->
                        <form action="{{ route('meeting.end', $meeting->id) }}" method="POST" class="flex-1" onsubmit="return confirm('Are you sure you want to cancel this scheduled meeting?')">
                            @csrf
                            <button type="submit" class="w-full h-11 px-4 rounded-xl border-2 border-slate-950 text-red-400 hover:text-red-300 hover:bg-slate-800 font-semibold text-sm transition active:scale-95 shadow-md flex items-center justify-center">
                                Cancel Meeting
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Countdown & Polling Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const startAtTime = new Date("{{ \Carbon\Carbon::parse($meeting->start_at)->toIso8601String() }}").getTime();
            const roomId = "{{ $meeting->room_id }}";

            // 1. Live Countdown Logic
            function updateCountdown() {
                const now = new Date().getTime();
                const diff = startAtTime - now;

                if (diff <= 0) {
                    // Time has arrived! Automatically enter the meeting by reloading the page
                    window.location.reload();
                    return;
                }

                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                const formattedTime = 
                    String(hours).padStart(2, '0') + ':' + 
                    String(minutes).padStart(2, '0') + ':' + 
                    String(seconds).padStart(2, '0');
                document.getElementById('countdownTimer').textContent = formattedTime;
            }

            // Initial call and set interval
            updateCountdown();
            const countdownInterval = setInterval(updateCountdown, 1000);

            // 2. Polling for Meeting Status (Cancellation detection)
            function pollStatus() {
                fetch(`/meeting/status/${roomId}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'deleted') {
                        // The meeting was cancelled/deleted by the host!
                        clearInterval(countdownInterval);
                        clearInterval(pollingInterval);
                        
                        alert('This scheduled meeting has been cancelled by the host.');
                        window.location.href = "{{ route('dashboard') }}";
                    }
                })
                .catch(err => console.error('Status poll failed:', err));
            }

            // Poll every 3 seconds
            const pollingInterval = setInterval(pollStatus, 3000);
        });
    </script>
</x-app-layout>
