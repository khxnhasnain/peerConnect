<x-app-layout>
    <div id="meetingRoot" class="h-screen w-screen flex flex-col bg-slate-950 text-slate-100 overflow-hidden">

        <div class="h-20 bg-slate-900 px-4 md:px-6 flex justify-between items-center border-b border-slate-800 z-20 shadow-md">
            <div class="flex items-center gap-3 flex-wrap">
                <div class="flex items-center justify-center shrink-0">
                    <img src="/images/logo.png" class="h-14 w-auto rounded-lg shadow-sm object-contain" alt="Logo">
                </div>

                <div class="flex items-center gap-3 text-slate-200 font-medium select-none text-[17px] pl-2">
                    <span id="meetingTimer" class="font-semibold text-slate-100">14:01</span>
                    <span class="text-white font-light">|</span>
                    <div class="flex items-center gap-1.5">
                        <span class="font-semibold select-all font-mono lowercase text-slate-100">{{ $meeting->room_id ?? 'N/A' }}</span>
                        <button id="copyLinkBtn" style="width: 16px; height: 16px; min-width: 16px; min-height: 16px; background-color: transparent; border: 2px solid #ffffff; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; margin-left: 10px; transition: background-color 0.2s, transform 0.1s; outline: none;" onmouseover="this.style.backgroundColor='rgba(255,255,255,0.15)'" onmouseout="this.style.backgroundColor='transparent'" onmousedown="this.style.transform='scale(0.9)'" onmouseup="this.style.transform='scale(1)'" title="Copy Meeting Link">
                            <span style="font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 10px; color: #cbd5e1; line-height: 1; display: inline-block; transform: translateY(0.5px);">i</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button id="participantCountBtn" type="button" class="h-10 px-4 rounded-full bg-indigo-600 hover:bg-indigo-500 border-2 border-indigo-800 text-white text-sm font-semibold transition cursor-pointer active:scale-95 shadow-md flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="currentColor">
                        <!-- Top person -->
                        <circle cx="12" cy="6" r="2.5" />
                        <path d="M12 9.5c-2 0-3.5 1-3.5 2.5v.5h7v-.5c0-1.5-1.5-2.5-3.5-2.5z" />
                        <!-- Bottom center person -->
                        <circle cx="12" cy="15.5" r="2.5" />
                        <path d="M12 19c-2.5 0-4 1.2-4 2.8v.7h8v-.7c0-1.6-1.5-2.8-4-2.8z" />
                        <!-- Bottom left person -->
                        <circle cx="6.5" cy="14.5" r="2" />
                        <path d="M6.5 17.5c-1.8 0-3 1-3 2.2v.8h6v-.8c0-1.2-1.2-2.2-3-2.2z" />
                        <!-- Bottom right person -->
                        <circle cx="17.5" cy="14.5" r="2" />
                        <path d="M17.5 17.5c-1.8 0-3 1-3 2.2v.8h6v-.8c0-1.2-1.2-2.2-3-2.2z" />
                    </svg>
                    <span id="participantCount" class="font-mono">1</span>
                </button>
                @if(Auth::id() == $meeting->created_by)
                <button id="endMeetingBtn" class="h-10 px-4 rounded-full bg-indigo-600 hover:bg-indigo-500 border-2 border-indigo-800 text-white text-sm font-semibold flex items-center gap-2 transition active:scale-95 shadow-md">
                    End Meeting
                </button>
                @endif
            </div>
        </div>

        <div class="flex flex-1 w-full overflow-hidden relative bg-slate-950">

            <div class="flex-1 flex flex-col justify-between overflow-hidden bg-slate-950">

                <div class="flex-1 p-4 md:p-6 overflow-hidden flex flex-col justify-center">
                    <div id="gridContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 items-center justify-center auto-rows-auto max-w-6xl mx-auto w-full h-full max-h-full overflow-y-auto">

                        <div class="relative w-full max-w-[360px] mx-auto bg-slate-900 rounded-xl overflow-hidden border border-slate-800 shadow-md aspect-video cursor-pointer">
                            <video id="localVideo" autoplay playsinline muted class="w-full h-full object-cover"></video>
                            <span class="absolute bottom-3 left-3 bg-slate-950/80 backdrop-blur text-xs px-3 py-1.5 rounded-lg border border-slate-800 text-slate-200 font-medium shadow-md">You</span>
                        </div>

                        <div id="waitingMessage" class="hidden text-center text-slate-400 py-10 col-span-full">
                            <p class="text-xl mb-1 text-slate-200 font-semibold">Room Setup Complete</p>
                            <p class="text-sm text-slate-500">Waiting for other participants to connect...</p>
                        </div>
                    </div>
                </div>

                <div class="w-full pb-4 pt-3 px-4 md:px-6 flex justify-center z-10 shrink-0 bg-slate-900/95 border-t border-slate-800 shadow-md">
                    <div class="flex items-center justify-center gap-4 whitespace-nowrap bg-slate-900 border border-slate-800 shadow-2xl rounded-xl px-6 py-2.5">
                        <button id="toggleAudioBtn" class="bg-black hover:bg-zinc-900 border-2 border-zinc-700 text-white p-3 rounded-full w-14 h-14 flex items-center justify-center transition active:scale-95 shadow-md" title="Mute/Unmute Microphone">
                            <svg id="micOnIcon" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path>
                                <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                                <line x1="12" y1="19" x2="12" y2="23"></line>
                                <line x1="8" y1="23" x2="16" y2="23"></line>
                            </svg>

                            <svg id="micOffIcon" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                                <path d="M9 9v3a3 3 0 0 0 5.12 2.12M15 9.34V4a3 3 0 0 0-5.94-.6"></path>
                                <path d="M17 16.95A7 7 0 0 1 5 12v-2m14 0v2a7 7 0 0 1-.11 1.23"></path>
                                <line x1="12" y1="19" x2="12" y2="23"></line>
                                <line x1="8" y1="23" x2="16" y2="23"></line>
                            </svg>
                        </button>
                        <button id="toggleVideoBtn" class="bg-black hover:bg-zinc-900 border-2 border-zinc-700 text-white p-3 rounded-full w-14 h-14 flex items-center justify-center transition active:scale-95 shadow-md" title="Turn Camera On/Off">
                            <svg id="camOnIcon" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M14 6a3 3 0 0 1 3 3v1.75l4.62-2.31A1 1 0 0 1 23 9.33v5.34a1 1 0 0 1-1.38.92L17 13.25V15a3 3 0 0 1-3 3H4a3 3 0 0 1-3-3V9a3 3 0 0 1 3-3h10z"/>
                            </svg>
                            <svg id="camOffIcon" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 hidden" viewBox="0 0 24 24" fill="currentColor">
                                <defs>
                                    <mask id="camera-slash-mask">
                                        <rect width="24" height="24" fill="white" />
                                        <line x1="3" y1="21" x2="21" y2="3" stroke="black" stroke-width="3.5" stroke-linecap="round" />
                                    </mask>
                                </defs>
                                <path d="M14 6a3 3 0 0 1 3 3v1.75l4.62-2.31A1 1 0 0 1 23 9.33v5.34a1 1 0 0 1-1.38.92L17 13.25V15a3 3 0 0 1-3 3H4a3 3 0 0 1-3-3V9a3 3 0 0 1 3-3h10z" mask="url(#camera-slash-mask)"/>
                                <line x1="3" y1="21" x2="21" y2="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none" />
                            </svg>
                        </button>
                        <button id="screenShareBtn" class="bg-black hover:bg-zinc-900 border-2 border-zinc-700 text-white p-3 rounded-full w-14 h-14 flex items-center justify-center transition active:scale-95 shadow-md" title="Share Your Screen Now">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                                <defs>
                                    <mask id="screen-arrow-mask">
                                        <rect width="24" height="24" fill="white" />
                                        <g fill="black">
                                            <rect x="10.75" y="9" width="2.5" height="6.5" rx="1.25" />
                                            <path d="M12 5.25a1.25 1.25 0 0 1 .88.37l3.5 3.5a1.25 1.25 0 1 1-1.76 1.76L12 8.26l-2.62 2.62a1.25 1.25 0 1 1-1.76-1.76l3.5-3.5a1.25 1.25 0 0 1 .88-.37z" />
                                        </g>
                                    </mask>
                                </defs>
                                <rect x="1.5" y="3.5" width="21" height="14" rx="3.5" mask="url(#screen-arrow-mask)" />
                                <path d="M7 17.5c0 1.38 1.12 2.5 2.5 2.5h5c1.38 0 2.5-1.12 2.5-2.5v-0.5H7v0.5z M8.5 17.5h7v0.5c0 .55-.45 1-1 1h-5c-.55 0-1-.45-1-1v-0.5z" fill-rule="evenodd" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <button id="raiseHandBtn" class="bg-black hover:bg-zinc-900 border-2 border-zinc-700 text-white p-3 rounded-full w-14 h-14 flex items-center justify-center transition active:scale-95 shadow-md" title="Raise Hand">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 11V3a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v0"></path>
                                <path d="M14 10V1a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v2"></path>
                                <path d="M10 10.5V2a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v8"></path>
                                <path d="M6 14v-1.5a1.5 1.5 0 0 0-3 0V16a6 6 0 0 0 6 6h6.5A5.5 5.5 0 0 0 21 16.5v-2a1.5 1.5 0 0 0-3 0"></path>
                            </svg>
                        </button>
                        <button id="recordBtn" class="bg-black hover:bg-zinc-900 border-2 border-zinc-700 text-white p-3 rounded-full w-14 h-14 flex items-center justify-center transition active:scale-95 shadow-md" title="Record Meeting">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <circle cx="12" cy="12" r="3" fill="currentColor" />
                            </svg>
                        </button>
                        <div style="position: relative; width: 56px; height: 56px; display: inline-block;">
                            <button id="toggleChatBtn" class="bg-black hover:bg-zinc-900 border-2 border-zinc-700 text-white p-3 rounded-full w-full h-full flex items-center justify-center transition active:scale-95 shadow-md" title="Toggle Chat Panel">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/>
                                </svg>
                            </button>
                            <span id="chatNotificationDot" class="hidden" style="position: absolute; top: -5px; right: -5px; width: 22px; height: 22px; background-color: #0f172a; border: 2px solid #020617; border-radius: 50%; color: #ffffff; font-size: 10px; font-weight: bold; display: flex; align-items: center; justify-content: center; z-index: 30; pointer-events: none; line-height: 1; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">0</span>
                        </div>
                        <button id="leaveMeetingBtn" class="bg-red-600 hover:bg-red-500 border-2 border-red-800 text-white p-3 rounded-full w-14 h-14 flex items-center justify-center transition active:scale-95 shadow-md" title="Leave Meeting">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" transform="rotate(135 12 12)" />
                            </svg>
                        </button>
                    </div>
                </div>

            </div>

            <div id="recordingStatus" class="hidden fixed top-20 right-4 bg-rose-600 text-white px-4 py-2 rounded-lg shadow-lg z-50">
                <span class="flex items-center gap-2">
                    <span class="w-2 h-2 bg-white rounded-full animate-pulse"></span>
                    Recording...
                    <span id="recordingTimer" class="ml-2 font-mono">00:00</span>
                </span>
            </div>

            <div id="chatPanel" class="hidden w-80 h-full bg-slate-900 border-l border-slate-800 flex flex-col shadow-xl transition-all duration-300 shrink-0">
                <div id="participantsSection" class="flex-1 flex flex-col overflow-hidden">
                    <div class="p-4 border-b border-slate-800 bg-slate-900">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Participants</h2>
                            <span id="participantsBadge" class="text-[11px] text-slate-400">0</span>
                        </div>
                        <div id="participantsList" class="mt-3 space-y-2 overflow-y-auto"></div>
                    </div>
                </div>

                <div id="chatSection" class="hidden flex-1 flex flex-col overflow-hidden">
                    <div class="p-4 border-b border-slate-800 bg-slate-900">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Meeting Chat</h2>
                    </div>

                    <div id="chatMessages" class="flex-1 p-4 overflow-y-auto space-y-3 scrollbar-thin bg-slate-950/60 shadow-inner"></div>

                    <div class="w-full h-18 px-4 border-t border-slate-800 bg-slate-900 flex gap-2 items-center shrink-0">
                        <input type="text"
                            id="chatInput"
                            placeholder="Type a message..."
                            class="w-full bg-white px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-600 text-sm text-slate-900 placeholder-slate-400 transition shadow-sm">
                        <button id="sendChatBtn" class="h-10 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 border border-indigo-700/50 text-white text-sm font-semibold transition active:scale-95 shrink-0 shadow-md">
                            Send
                        </button>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Hidden inputs -->
    <input type="hidden" id="meetingId" value="{{ $meeting->id }}">
    <input type="hidden" id="authUserId" value="{{ auth()->user()->id }}">
    <input type="hidden" id="userName" value="{{ auth()->user()->name }}">
    <input type="hidden" id="csrfToken" value="{{ csrf_token() }}">
    <input type="hidden" id="isHost" value="{{ Auth::id() == $meeting->created_by ? '1' : '0' }}">
    <input type="hidden" id="authAvatar" value="{{ auth()->user()->avatar }}">
    <input type="hidden" id="initialPeerId" value="{{ $participant->peer_id ?? '' }}">

    <script src="https://unpkg.com/simple-peer@9.11.1/simplepeer.min.js"></script>
    <script>
        // ============================================================
        // READ VALUES
        // ============================================================
        const meetingId = parseInt(document.getElementById('meetingId').value) || 0;
        const authUserId = parseInt(document.getElementById('authUserId').value) || 0;
        const csrfToken = document.getElementById('csrfToken').value || '';
        const isHost = document.getElementById('isHost').value === '1';
        const myName = document.getElementById('userName').value || 'User';

        console.log('Meeting ID:', meetingId);
        console.log('User ID:', authUserId);
        console.log('Is Host:', isHost);
        console.log('My Name:', myName);

        // ============================================================
        // GLOBAL VARIABLES
        // ============================================================
        let peers = {};
        let localStream = null;
        let videoElements = {};
        let participantNames = {};
        let participantAvatars = {};
        let myAvatar = document.getElementById('authAvatar')?.value || '';
        let participantListState = [];
        let sidebarView = 'participants';
        let isEnded = false;
        let unreadChatCount = 0;
        let refreshInterval = null;
        let myPeerId = null;
        let isConnecting = {};
        let pendingSignals = {};
        let failedPeers = {};
        let isScreenSharing = false;
        let screenStream = null;

        // Recording variables
        let mediaRecorder = null;
        let recordedChunks = [];
        let isRecording = false;
        let recordingStream = null;
        let recordingCaptureStream = null;
        let recordingTimer = null;
        let recordingSeconds = 0;
        let recordingApprovedByHost = isHost;
        let recordingRejectedByHost = false;
        let recordingRequestTimer = null;
        const originalRecordBtnHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <circle cx="12" cy="12" r="3" fill="currentColor" />
            </svg>
        `;

        function resetRecordButton() {
            const recordBtn = document.getElementById('recordBtn');
            if (!recordBtn) return;

            if (recordingRejectedByHost) {
                recordBtn.disabled = true;
                recordBtn.innerHTML = 'Rejected';
                recordBtn.className = 'bg-slate-800 border-2 border-slate-800 text-slate-500 p-3 rounded-full w-14 h-14 flex items-center justify-center cursor-not-allowed shadow-md';
            } else {
                recordBtn.disabled = false;
                recordBtn.innerHTML = originalRecordBtnHTML;
                recordBtn.className = 'bg-slate-800 hover:bg-slate-700 border-2 border-slate-800 text-slate-100 p-3 rounded-full w-14 h-14 flex items-center justify-center transition active:scale-95 shadow-md';
            }
        }

        function clearRecordingRequestTimer() {
            if (recordingRequestTimer) {
                clearTimeout(recordingRequestTimer);
                recordingRequestTimer = null;
            }
        }

        // Hand raise state
        window.isHandRaised = false;
        window.handRaiseStates = {};
        window.lastRealtimeHandRaiseTime = {};

        // Chat state
        let lastChatMessageId = 0;
        const receivedChatIds = new Set();
        let syncInterval = null;
        let echoListenersInitialized = false;
        let activeMeetingChannel = null;
        let isSyncing = false;

        const recentMessages = [];

        function handleIncomingChatMessage(msg) {
            if (!msg || !msg.message) return;

            // Normalize fields
            const id = msg.id || msg.message_id;
            const userId = msg.user_id || msg.userId;
            const senderName = msg.sender_name || msg.senderName || 'User';

            // Clean up old messages from recent cache (older than 10 seconds)
            const now = Date.now();
            while (recentMessages.length > 0 && now - recentMessages[0].time > 10000) {
                recentMessages.shift();
            }

            // Check if this message was recently received
            const isDuplicate = recentMessages.some(m => m.userId == userId && m.message === msg.message);
            if (isDuplicate) {
                // If it has a database ID, store it to prevent future polling duplicates
                if (id) {
                    receivedChatIds.add(id);
                    lastChatMessageId = Math.max(lastChatMessageId, id);
                }
                return;
            }

            // Add to recent cache
            recentMessages.push({
                userId: userId,
                message: msg.message,
                time: now
            });

            // Store ID if available
            if (id) {
                receivedChatIds.add(id);
                lastChatMessageId = Math.max(lastChatMessageId, id);
            }

            if (userId == authUserId) return;

            appendMessage(senderName, msg.message, false);

            // Show notification dot if chat panel is not active
            const panel = document.getElementById('chatPanel');
            const isChatVisible = panel && !panel.classList.contains('hidden') && sidebarView === 'chat';
            if (!isChatVisible) {
                unreadChatCount++;
                const dot = document.getElementById('chatNotificationDot');
                if (dot) {
                    dot.textContent = unreadChatCount;
                    dot.classList.remove('hidden');
                }
                updateSidebarButtonsUI();
            }
        }

        function handleIncomingMediaStateChange(payload) {
            const peerId = payload.peer_id;
            const boxId = 'video-' + peerId;
            const videoBox = document.getElementById(boxId);
            if (videoBox) {
                // Update mute badge
                let badge = videoBox.querySelector('.mute-indicator');
                if (payload.is_audio_muted) {
                    if (!badge) {
                        badge = document.createElement('span');
                        badge.className = 'mute-indicator';
                        badge.style.cssText = 'position: absolute; top: 12px; right: 12px; width: 32px; height: 32px; border-radius: 50%; background-color: #ffffff; border: 1.5px solid #d1d5db; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); z-index: 20;';
                        badge.innerHTML = `
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2.5" xmlns="http://www.w3.org/2000/svg">
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                                <path d="M9 9v3a3 3 0 0 0 5.12 2.12M15 9.34V4a3 3 0 0 0-5.94-.6"></path>
                                <path d="M17 16.95A7 7 0 0 1 5 12v-2m14 0v2a7 7 0 0 1-.11 1.23"></path>
                                <line x1="12" y1="19" x2="12" y2="23"></line>
                                <line x1="8" y1="23" x2="16" y2="23"></line>
                            </svg>
                        `;
                        videoBox.appendChild(badge);
                    }
                } else if (badge) {
                    badge.remove();
                }

                // Update avatar placeholder
                updateAvatarPlaceholder(videoBox, {
                    is_video_off: payload.is_video_off,
                    user_name: participantNames[peerId] || 'User',
                    user_avatar: participantAvatars[peerId]
                });
            }
        }

        function handlePeerData(payload, fromPeerId) {
            if (!payload || !payload.type) return;

            if (payload.type === 'chat_message') {
                handleIncomingChatMessage(payload);
                return;
            }

            if (payload.type === 'hand_raised') {
                handleIncomingHandRaise({
                    userId: payload.user_id,
                    peerId: payload.peer_id || fromPeerId,
                    raised: !!payload.raised,
                    userName: payload.user_name
                });
                return;
            }

            if (payload.type === 'media_state_changed') {
                handleIncomingMediaStateChange(payload);
                return;
            }
        }

        function broadcastToConnectedPeers(payload) {
            Object.keys(peers).forEach(function(peerId) {
                const peer = peers[peerId];
                if (!peer) return;

                const sendPayload = function() {
                    try {
                        peer.send(JSON.stringify(payload));
                    } catch (e) {
                        console.warn('Peer data send failed:', peerId, e);
                    }
                };

                if (peer.connected) {
                    sendPayload();
                } else {
                    peer.once('connect', sendPayload);
                }
            });
        }

        // ============================================================
        // UTILITY FUNCTIONS
        // ============================================================
        function sanitizeSDP(sdp) {
            if (!sdp || typeof sdp !== 'string') return sdp;
            const cleaned = sdp.replace(/\r\n/g, '\n').split('\n').filter(line => line.length > 0).join('\r\n');
            return cleaned.endsWith('\r\n') ? cleaned : cleaned + '\r\n';
        }

        function sanitizeSignal(signalData) {
            if (!signalData || typeof signalData !== 'object') return signalData;
            const cleanedSignal = Object.assign({}, signalData);
            if (typeof cleanedSignal.sdp === 'string') {
                cleanedSignal.sdp = sanitizeSDP(cleanedSignal.sdp);
            }
            return cleanedSignal;
        }

        // ============================================================
        // CAMERA
        // ============================================================
        function startCamera() {
            if (localStream) return;

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert('Camera requires HTTPS or localhost.');
                return;
            }

            navigator.mediaDevices.getUserMedia({
                    audio: true,
                    video: {
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    }
                })
                .then(function(stream) {
                    localStream = stream;

                    // Mute mic track initially
                    const audioTrack = localStream.getAudioTracks()[0];
                    if (audioTrack) {
                        audioTrack.enabled = false;
                    }

                    addVideo('local', stream, true);
                    console.log('Camera started successfully');

                    // Sync initially muted state to server
                    syncStatusToServer();

                    // Dynamically push local stream to all active peer connections
// Removed dynamic stream addition to peers; streams are now attached during peer connection creation.


                    // Process any queued peer connection requests now that the stream is ready
                    pendingPeerConnectionRequests.forEach(function(req) {
                        createPeerConnection(req.peerId, req.isInitiator, req.customStream);
                    });
                    pendingPeerConnectionRequests = [];
                })
                .catch(function(err) {
                    console.error('Camera error:', err);
                    alert('Please allow camera access and refresh.');
                });
        }

        // ============================================================
        // GENERATE PEER ID
        // ============================================================
        function generatePeerId() {
            myPeerId = 'user-' + authUserId + '-' + Date.now();
            console.log('MY PEER ID:', myPeerId);

            return fetch('/save-peer-id', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        peer_id: myPeerId,
                        meeting_id: meetingId
                    })
                })
                .then(function() {
                    console.log('Peer ID saved to server');

                    const localContainer = document.getElementById('localVideo')?.parentElement;
                    if (localContainer && myPeerId) {
                        localContainer.id = 'video-' + myPeerId;
                        localContainer.dataset.userId = authUserId;
                        videoElements[myPeerId] = localContainer;
                        delete videoElements['local'];
                    }

                    runSync();
                    initEchoListeners();
                    startSyncPolling();
                })
                .catch(function(err) {
                    console.error('Error saving peer ID:', err);
                });
        }

        // ============================================================
        // PEER CONNECTION
        // ============================================================
        function createPeerConnection(peerId, isInitiator, customStream = null) {
            if (peers[peerId]) {
                console.log('Already connected to:', peerId);
                return;
            }
            if (isConnecting[peerId]) {
                console.log('Already connecting to:', peerId);
                return;
            }
            // If the local camera stream is not yet ready, queue the request
            if (!localStream) {
                console.log('Camera not ready – queuing connection for:', peerId);
                pendingPeerConnectionRequests.push({peerId, isInitiator, customStream});
                return;
            }

            console.log('Creating peer connection to:', peerId, '| Initiator:', isInitiator);
            isConnecting[peerId] = true;

            // Determine what stream to send
            let streamToSend = null;
            if (peerId.endsWith('-screen')) {
                if (isInitiator) {
                    streamToSend = customStream; // our screenStream
                } else {
                    streamToSend = null; // we are just receiving the remote screen
                }
            } else {
                streamToSend = localStream; // standard camera connection
            }

            const connectionTimeout = setTimeout(function() {
                if (isConnecting[peerId] && (!peers[peerId] || !peers[peerId].connected)) {
                    console.warn('Connection attempt timed out for:', peerId);
                    isConnecting[peerId] = false;
                    failedPeers[peerId] = Date.now();
                    try {
                        if (peers[peerId]) peers[peerId].destroy();
                    } catch (e) {}
                    delete peers[peerId];
                }
            }, 30000); // 30 seconds timeout for slower connections

            const peer = new SimplePeer({
                initiator: isInitiator,
                stream: streamToSend,
                trickle: true,
                sdpTransform: sanitizeSDP,
                config: {
                    iceServers: [{
                            urls: 'stun:stun.l.google.com:19302'
                        },
                        {
                            urls: 'stun:stun1.l.google.com:19302'
                        },
                        {
                            urls: 'stun:stun2.l.google.com:19302'
                        }
                    ]
                }
            });

            peer.on('signal', function(data) {
                console.log(' Sending signal to:', peerId);

                // Determine source and target peer IDs for signaling
                let targetPeerId = peerId;
                let sourcePeerId = myPeerId;

                if (peerId.endsWith('-screen')) {
                    if (isInitiator) {
                        targetPeerId = peerId.replace('-screen', '');
                        sourcePeerId = myPeerId + '-screen';
                    } else {
                        targetPeerId = peerId;
                        sourcePeerId = myPeerId;
                    }
                }

                sendSignal(targetPeerId, data, sourcePeerId);
            });

            peer.on('stream', function(stream) {
                console.log(' REMOTE STREAM RECEIVED from:', peerId);
                delete failedPeers[peerId];
                clearTimeout(connectionTimeout);

                if (videoElements[peerId]) {
                    const videoEl = videoElements[peerId].querySelector('video');
                    if (videoEl) {
                        videoEl.srcObject = stream;
                        videoEl.play().catch(function() {});
                        console.log('Updated existing video for:', peerId);
                    }
                } else {
                    addVideo(peerId, stream, false);
                }

                isConnecting[peerId] = false;
            });

            peer.on('connect', function() {
                console.log(' Connected to:', peerId);
                isConnecting[peerId] = false;
                clearTimeout(connectionTimeout);
            });

            peer.on('data', function(data) {
                try {
                    const payload = JSON.parse(data.toString());
                    console.log('Peer data received:', payload);
                    handlePeerData(payload, peerId);
                } catch (e) {
                    console.warn('Failed to parse peer data:', e);
                }
            });

            peer.on('close', function() {
                console.log('Peer closed connection:', peerId);
                clearTimeout(connectionTimeout);
                removeVideo(peerId);
                delete peers[peerId];
                delete isConnecting[peerId];
            });

            peer.on('error', function(err) {
                console.error('Peer connection error:', err);
                clearTimeout(connectionTimeout);
                isConnecting[peerId] = false;
                failedPeers[peerId] = Date.now();
                try {
                    peer.destroy();
                } catch (e) {}
                delete peers[peerId];
            });

            peers[peerId] = peer;

            if (pendingSignals[peerId] && pendingSignals[peerId].length) {
                setTimeout(function() {
                    if (!peers[peerId]) return;
                    pendingSignals[peerId].forEach(function(signal) {
                        try {
                            peers[peerId].signal(signal);
                        } catch (e) {}
                    });
                    delete pendingSignals[peerId];
                }, 0);
            }
        }

        // ============================================================
        // SIGNALING
        // ============================================================
        function sendSignal(peerId, signalData, fromPeerId = null) {
            const senderId = fromPeerId || myPeerId;
            const cleanSignal = sanitizeSignal(signalData);

            if (typeof cleanSignal === 'object' && cleanSignal !== null) {
                cleanSignal._from_peer_id = senderId;
                cleanSignal._to_peer_id = peerId;
            }

            // 1. Send via WebSocket Echo Whisper (instant realtime delivery)
            if (activeMeetingChannel && typeof activeMeetingChannel.whisper === 'function') {
                activeMeetingChannel.whisper('webrtc-signal', {
                    from_peer_id: senderId,
                    to_peer_id: peerId,
                    signal_data: cleanSignal
                });
                console.log('Signal sent via WebSocket Whisper to:', peerId);
            }

            // 2. HTTP POST database fallback
            fetch('/meeting/signal', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    meeting_id: meetingId,
                    peer_id: senderId,
                    target_peer_id: peerId,
                    signal: cleanSignal
                })
            }).catch(err => console.error('Error sending signal:', err));
        }

        function broadcastSignalToAllPeers(payload) {
            if (!myPeerId || !participantListState.length) return;

            participantListState.forEach(function(participant) {
                if (participant.user_id == authUserId || !participant.peer_id) return;

                fetch('/meeting/signal', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        meeting_id: meetingId,
                        peer_id: myPeerId,
                        target_peer_id: participant.peer_id,
                        signal: payload
                    })
                }).catch(function(err) {
                    console.error('Failed to relay signal to peer:', participant.peer_id, err);
                });
            });
        }

        function processSignal(fromPeerId, signalData) {
            let parsedSignal = signalData;
            if (typeof signalData === 'string') {
                try {
                    parsedSignal = JSON.parse(signalData);
                } catch (e) {}
            }
            parsedSignal = sanitizeSignal(parsedSignal);

            if (parsedSignal && parsedSignal.type === 'chat_message') {
                appendMessage(parsedSignal.sender_name, parsedSignal.message, false);
                return;
            }

            if (parsedSignal && parsedSignal.type === 'hand_raised') {
                handleIncomingHandRaise({
                    userId: parsedSignal.user_id,
                    peerId: parsedSignal.peer_id || fromPeerId,
                    raised: !!parsedSignal.raised,
                    userName: parsedSignal.user_name
                });
                return;
            }

            if (parsedSignal && parsedSignal.type === 'request_recording_permission') {
                if (isHost) {
                    showRecordingRequestModal(parsedSignal.requesterName, parsedSignal.requesterPeerId, parsedSignal.requesterUserId);
                }
                return;
            }

            if (parsedSignal && parsedSignal.type === 'recording_permission_response') {
                clearRecordingRequestTimer();

                const recordBtn = document.getElementById('recordBtn');

                if (parsedSignal.approved) {
                    recordingApprovedByHost = true;
                    resetRecordButton();
                    alert('Host approved your recording request. Starting recording now...');
                    startRecording();
                } else {
                    recordingApprovedByHost = false;
                    recordingRejectedByHost = true;
                    resetRecordButton();
                    alert('Host rejected your recording request. You cannot request again.');
                }
                return;
            }

            // Extract from/to peer IDs from custom payload properties if available
            const actualFromPeerId = (parsedSignal && parsedSignal._from_peer_id) || fromPeerId;
            const actualToPeerId = (parsedSignal && parsedSignal._to_peer_id) || null;

            let peerKey = actualFromPeerId;
            if (actualToPeerId && actualToPeerId.endsWith('-screen')) {
                if (actualToPeerId === myPeerId + '-screen') {
                    // We are the screen sharer, we store connections to remote peers as remotePeerId + '-screen'
                    peerKey = actualFromPeerId + '-screen';
                }
            } else if (actualFromPeerId && actualFromPeerId.endsWith('-screen')) {
                // We are receiving the screenshare
                peerKey = actualFromPeerId;
            }

            if (!peers[peerKey]) {
                if (peerKey.endsWith('-screen')) {
                    // Create peer connection to receive screenshare (initiator: false, customStream: null)
                    if (!participantNames[peerKey]) {
                        const ownerPeerId = peerKey.replace('-screen', '');
                        const ownerName = participantNames[ownerPeerId] || 'User';
                        participantNames[peerKey] = ownerName + "'s Screen";
                    }
                    createPeerConnection(peerKey, false, null);
                } else {
                    pendingSignals[peerKey] = pendingSignals[peerKey] || [];
                    pendingSignals[peerKey].push(parsedSignal);
                    createPeerConnection(peerKey, false);
                    return;
                }
            }

            try {
                peers[peerKey].signal(parsedSignal);
            } catch (e) {}
        }

        // ============================================================
        // PARTICIPANTS SYNC HANDLER
        // ============================================================
        function handleParticipantsSync(data) {
            if (!data) return;

            const count = data.length;
            document.getElementById('participantCount').textContent = count;
            const participantsBadge = document.getElementById('participantsBadge');
            if (participantsBadge) participantsBadge.textContent = count;

            // Check if current user was removed from the meeting
            const selfInList = data.some(function(p) {
                return p.user_id == authUserId;
            });

            if (!selfInList && !isHost) {
                endMeetingForAll('You have been removed from the meeting by the host');
                return;
            }

            if (count === 0 && !isHost) {
                endMeetingForAll('Meeting has ended');
                return;
            }

            participantListState = data;
            renderParticipantList(data);

            // Store participant names
            data.forEach(function(p) {
                if (p.user_id != authUserId) {
                    if (p.peer_id) {
                        participantNames[p.peer_id] = p.user_name;
                        participantNames[p.peer_id + '_userId'] = p.user_id;
                        participantAvatars[p.peer_id] = p.user_avatar;
                    }
                } else {
                    myAvatar = p.user_avatar;
                }
            });

            // Update mute/video status
            data.forEach(function(p) {
                const isMe = (p.user_id == authUserId);
                if (isMe) return; // Prevent overwriting local user's instant status updates

                const boxId = 'video-' + p.peer_id;
                const videoBox = document.getElementById(boxId);

                if (videoBox) {
                    let badge = videoBox.querySelector('.mute-indicator');
                    if (p.is_audio_muted) {
                        if (!badge) {
                            badge = document.createElement('span');
                            badge.className = 'mute-indicator';
                            badge.style.cssText = 'position: absolute; top: 12px; right: 12px; width: 32px; height: 32px; border-radius: 50%; background-color: #ffffff; border: 1.5px solid #d1d5db; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); z-index: 20;';
                            badge.innerHTML = `
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2.5" xmlns="http://www.w3.org/2000/svg">
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                    <path d="M9 9v3a3 3 0 0 0 5.12 2.12M15 9.34V4a3 3 0 0 0-5.94-.6"></path>
                                    <path d="M17 16.95A7 7 0 0 1 5 12v-2m14 0v2a7 7 0 0 1-.11 1.23"></path>
                                    <line x1="12" y1="19" x2="12" y2="23"></line>
                                    <line x1="8" y1="23" x2="16" y2="23"></line>
                                </svg>
                            `;
                            videoBox.appendChild(badge);
                        }
                    } else if (badge) {
                        badge.remove();
                    }

                    updateAvatarPlaceholder(videoBox, p);

                    // Sync hand raised status for REMOTE and LOCAL users.
                    if (!isMe) {
                        const key = p.peer_id || String(p.user_id);
                        const lastSignalTime = window.lastRealtimeHandRaiseTime ? window.lastRealtimeHandRaiseTime[p.user_id] : 0;
                        const isCooldown = lastSignalTime && (Date.now() - lastSignalTime < 3000);

                        if (!isCooldown) {
                            const handRaised = !!p.hand_raised;
                            const hasBadge = !!videoBox.querySelector('.hand-raise-badge');
                            if (window.handRaiseStates[key] !== handRaised || hasBadge !== handRaised) {
                                window.handRaiseStates[key] = handRaised;
                                globalRenderHandRaise(videoBox, handRaised);
                            }
                        }
                    } else {
                        const lastSignalTime = window.lastRealtimeHandRaiseTime ? window.lastRealtimeHandRaiseTime[authUserId] : 0;
                        const isCooldown = lastSignalTime && (Date.now() - lastSignalTime < 3000);

                        if (!isCooldown && window.isHandRaised !== !!p.hand_raised) {
                            window.isHandRaised = !!p.hand_raised;

                            // Keep visual button state in sync if poll state changed (e.g. host lowered it or page reloaded)
                            const raiseHandBtn = document.getElementById('raiseHandBtn');
                            if (raiseHandBtn) {
                                if (window.isHandRaised) {
                                    raiseHandBtn.className = 'bg-indigo-600 text-white border-2 border-indigo-800 p-3 rounded-full shadow-lg w-14 h-14 flex items-center justify-center transition-colors';
                                } else {
                                    raiseHandBtn.className = 'bg-black hover:bg-zinc-900 border-2 border-zinc-700 text-white p-3 rounded-full w-14 h-14 flex items-center justify-center transition active:scale-95 shadow-md';
                                }
                            }
                            globalRenderHandRaise(videoBox, window.isHandRaised);
                        }
                    }
                } else if (!isMe) {
                    const key = p.peer_id || String(p.user_id);
                    const lastSignalTime = window.lastRealtimeHandRaiseTime ? window.lastRealtimeHandRaiseTime[p.user_id] : 0;
                    const isCooldown = lastSignalTime && (Date.now() - lastSignalTime < 3000);

                    if (!isCooldown) {
                        const handRaised = !!p.hand_raised;
                        if (window.handRaiseStates[key] !== handRaised) {
                            window.handRaiseStates[key] = handRaised;
                        }
                    }
                }
            });

            // Create connections for new participants
            data.forEach(function(p) {
                if (p.user_id != authUserId && p.peer_id && !peers[p.peer_id] && !isConnecting[p.peer_id]) {
                    const failedAt = failedPeers[p.peer_id];
                    if (failedAt && (Date.now() - failedAt) < 15000) return;

                    const shouldIInitiate = myPeerId > p.peer_id;

                    if (shouldIInitiate) {
                        console.log('Outgoing connection to:', p.user_name);
                        createPeerConnection(p.peer_id, true);
                    } else {
                        console.log('Expecting incoming connection from:', p.user_name);
                    }
                }
            });

            // If we are sharing screen, ensure we connect to all active participants
            if (isScreenSharing && screenStream) {
                data.forEach(function(p) {
                    if (p.user_id != authUserId && p.peer_id) {
                        const screenConnId = p.peer_id + '-screen';
                        if (!peers[screenConnId] && !isConnecting[screenConnId]) {
                            console.log('Initiating screen share connection to:', p.user_name);
                            createPeerConnection(screenConnId, true, screenStream);
                        }
                    }
                });
            }

            // Remove peers that are no longer in the meeting
            Object.keys(peers).forEach(function(peerId) {
                let checkId = peerId.endsWith('-screen') ? peerId.replace('-screen', '') : peerId;
                let exists = data.some(p => p.peer_id === checkId);
                if (!exists) {
                    console.log('Removing peer:', peerId);
                    removeVideo(peerId);
                }
            });
        }

        // ============================================================
        // UNIFIED SYNC POLLING LOOPS
        // ============================================================
        let currentSyncIntervalTime = 1000;

        function startSyncPolling() {
            if (syncInterval) clearInterval(syncInterval);
            runSync();
            syncInterval = setInterval(function() {
                if (!isEnded) runSync();
            }, currentSyncIntervalTime);
        }

        function adjustPollingInterval() {
            if (isEnded || !myPeerId) return;

            const otherParticipants = participantListState.filter(p => p.user_id != authUserId && p.peer_id).length;
            const connectedPeers = Object.keys(peers).filter(id => !id.endsWith('-screen')).length;

            // If we have unconnected participants, poll very fast (200ms) to exchange signals instantly
            // Otherwise, poll moderately (1000ms) to detect new joins faster while keeping server load low
            const targetInterval = (connectedPeers < otherParticipants) ? 200 : 1000;

            if (currentSyncIntervalTime !== targetInterval) {
                currentSyncIntervalTime = targetInterval;
                console.log('Adjusting sync polling interval to:', targetInterval, 'ms');
                
                if (syncInterval) clearInterval(syncInterval);
                syncInterval = setInterval(function() {
                    if (!isEnded) runSync();
                }, targetInterval);
            }
        }

        function runSync() {
            if (isEnded || !myPeerId || isSyncing) return;
            isSyncing = true;

            const url = '/meeting/sync/' + meetingId + '/' + myPeerId + (lastChatMessageId ? '?after_chat_id=' + lastChatMessageId : '');

            fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(res => {
                    if (res.status === 403) {
                        endMeetingForAll('You have been removed from the meeting');
                        throw new Error('Kicked from meeting');
                    }
                    if (res.status === 404) {
                        endMeetingForAll('Meeting has ended');
                        throw new Error('Meeting not found');
                    }
                    if (!res.ok) throw new Error('Sync failed');
                    return res.json();
                })
                .then(data => {
                    isSyncing = false;
                    if (!data) return;

                    // 1. Process signals
                    if (Array.isArray(data.signals)) {
                        data.signals.forEach(function(signal) {
                            processSignal(signal.from_peer_id, signal.signal_data);
                        });
                    }

                    // 2. Process participants
                    if (Array.isArray(data.participants)) {
                        handleParticipantsSync(data.participants);
                    }

                    // 3. Process chat messages
                    if (Array.isArray(data.messages)) {
                        data.messages.forEach(handleIncomingChatMessage);
                    }

                    // Dynamically adjust polling frequency depending on connection status
                    adjustPollingInterval();
                })
                .catch(err => {
                    isSyncing = false;
                    console.error('Sync error:', err);
                });
        }

        function updateSidebarButtonsUI() {
            const panel = document.getElementById('chatPanel');
            const chatBtn = document.getElementById('toggleChatBtn');
            if (!chatBtn) return;
            const isChatVisible = panel && !panel.classList.contains('hidden') && sidebarView === 'chat';
            if (isChatVisible) {
                const dot = document.getElementById('chatNotificationDot');
                if (dot) {
                    dot.classList.add('hidden');
                    dot.textContent = '0';
                }
                unreadChatCount = 0;
                chatBtn.className = 'bg-indigo-600 hover:bg-indigo-500 border-2 border-indigo-800 text-white p-3 rounded-full w-full h-full flex items-center justify-center transition active:scale-95 shadow-md';
            } else {
                if (unreadChatCount > 0) {
                    chatBtn.className = 'bg-blue-600 hover:bg-blue-500 border-2 border-blue-800 text-white p-3 rounded-full w-full h-full flex items-center justify-center transition active:scale-95 shadow-md';
                } else {
                    chatBtn.className = 'bg-black hover:bg-zinc-900 border-2 border-zinc-700 text-white p-3 rounded-full w-full h-full flex items-center justify-center transition active:scale-95 shadow-md';
                }
            }
        }

        function setSidebarView(view) {
            const panel = document.getElementById('chatPanel');
            const participantsSection = document.getElementById('participantsSection');
            const chatSection = document.getElementById('chatSection');
            if (!panel || !participantsSection || !chatSection) return;

            sidebarView = view;
            panel.classList.remove('hidden');

            if (view === 'chat') {
                participantsSection.classList.add('hidden');
                chatSection.classList.remove('hidden');
            } else {
                participantsSection.classList.remove('hidden');
                chatSection.classList.add('hidden');
            }
            updateSidebarButtonsUI();
        }

        function toggleSidebarView(view) {
            const panel = document.getElementById('chatPanel');
            if (!panel) return;

            if (panel.classList.contains('hidden')) {
                setSidebarView(view);
            } else if (sidebarView === view) {
                panel.classList.add('hidden');
            } else {
                setSidebarView(view);
            }
            updateSidebarButtonsUI();
        }

        function renderParticipantList(participants) {
            const container = document.getElementById('participantsList');
            if (!container) return;

            if (!Array.isArray(participants) || !participants.length) {
                container.innerHTML = '<div class="text-xs text-slate-500">No participants yet</div>';
                return;
            }

            container.innerHTML = '';

            participants.forEach(function(participant, index) {
                const isMe = participant.user_id == authUserId;
                const row = document.createElement('div');
                row.className = 'rounded-lg border border-slate-800 bg-slate-900/70 p-2.5';

                const topRow = document.createElement('div');
                topRow.className = 'flex items-center justify-between gap-2';

                const info = document.createElement('div');
                info.className = 'min-w-0';

                const titleRow = document.createElement('div');
                titleRow.className = 'flex items-center gap-2';

                const title = document.createElement('div');
                title.className = 'text-sm font-semibold text-slate-100 truncate';
                title.textContent = `${index + 1}. ${participant.user_name || 'User'}`;
                titleRow.appendChild(title);

                if (participant.hand_raised) {
                    const handBadge = document.createElement('span');
                    handBadge.className = 'inline-flex items-center rounded-full bg-amber-500/20 px-2 py-0.5 text-[10px] font-semibold text-amber-300';
                    handBadge.textContent = 'Hand raised';
                    titleRow.appendChild(handBadge);
                }

                if (participant.is_host) {
                    const hostBadge = document.createElement('span');
                    hostBadge.className = 'inline-flex items-center rounded-full bg-amber-500/15 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-amber-300';
                    hostBadge.textContent = 'Host';
                    titleRow.appendChild(hostBadge);
                }

                const meta = document.createElement('div');
                meta.className = 'text-[11px] text-slate-500';
                const metaText = isMe ? 'You' : (participant.is_audio_muted ? 'Muted' : 'Speaking');
                meta.textContent = metaText;

                info.appendChild(titleRow);
                info.appendChild(meta);
                topRow.appendChild(info);

                const actions = document.createElement('div');
                actions.className = 'flex items-center gap-1 shrink-0';

                const canModerate = isHost && !isMe;
                if (canModerate) {
                    const muteBtn = document.createElement('button');
                    muteBtn.type = 'button';
                    muteBtn.className = 'w-7 h-7 flex items-center justify-center rounded bg-slate-800 hover:bg-slate-700 text-slate-200';
                    const micOnSvg = `<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path><path d="M19 10v2a7 7 0 0 1-14 0v-2"></path><line x1="12" y1="19" x2="12" y2="23"></line><line x1="8" y1="23" x2="16" y2="23"></line></svg>`;
                    const micOffSvg = `<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="1" y1="1" x2="23" y2="23"></line><path d="M9 9v3a3 3 0 0 0 5.12 2.12M15 9.34V4a3 3 0 0 0-5.94-.6"></path><path d="M17 16.95A7 7 0 0 1 5 12v-2m14 0v2a7 7 0 0 1-.11 1.23"></path><line x1="12" y1="19" x2="12" y2="23"></line><line x1="8" y1="23" x2="16" y2="23"></line></svg>`;
                    muteBtn.innerHTML = participant.is_audio_muted ? micOffSvg : micOnSvg;
                    muteBtn.title = participant.is_audio_muted ? 'Unmute participant' : 'Mute participant';
                    muteBtn.addEventListener('click', function() {
                        toggleRemoteParticipantAudio(participant.user_id, participant.is_audio_muted);
                    });

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'w-7 h-7 flex items-center justify-center rounded bg-red-500/10 text-red-500 hover:bg-red-500/20 hover:text-red-400 transition-colors border border-red-500/20';
                    removeBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>`;
                    removeBtn.title = 'Remove participant';
                    removeBtn.addEventListener('click', function() {
                        removeParticipantFromMeeting(participant.user_id);
                    });

                    actions.appendChild(muteBtn);
                    actions.appendChild(removeBtn);
                }

                topRow.appendChild(actions);
                row.appendChild(topRow);
                container.appendChild(row);
            });
        }

        function toggleRemoteParticipantAudio(userId, isMuted) {
            if (!isHost) return;

            const previousState = participantListState.map(function(participant) {
                return Object.assign({}, participant);
            });
            participantListState = participantListState.map(function(participant) {
                if (participant.user_id == userId) {
                    participant.is_audio_muted = !isMuted;
                }
                return participant;
            });
            renderParticipantList(participantListState);

            fetch(`/meeting/toggle-audio/${meetingId}/${userId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        is_audio_muted: !isMuted
                    })
                })
                .catch(function(err) {
                    console.error('Failed to update participant audio:', err);
                    participantListState = previousState;
                    renderParticipantList(participantListState);
                    alert('Unable to update participant audio right now.');
                });
        }

        function removeParticipantFromMeeting(userId) {
            if (!isHost) return;
            if (!confirm('Remove this participant from the meeting?')) return;

            const previousState = participantListState.map(function(participant) {
                return Object.assign({}, participant);
            });
            const removedParticipant = participantListState.find(function(participant) {
                return participant.user_id == userId;
            });

            participantListState = participantListState.filter(function(participant) {
                return participant.user_id != userId;
            });
            renderParticipantList(participantListState);

            if (removedParticipant && removedParticipant.peer_id) {
                removeVideo(removedParticipant.peer_id);
            }

            fetch(`/meeting/kick/${meetingId}/${userId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(function(response) {
                    if (!response.ok) throw new Error('Unable to remove participant');
                    if (window.Echo) {
                        const meetingChannel = getMeetingChannel();
                        if (meetingChannel && typeof meetingChannel.whisper === 'function') {
                            meetingChannel.whisper('participant-kicked', {
                                user_id: userId,
                                message: 'You have been removed from the meeting by the host.'
                            });
                        }
                    }
                    return runSync();
                })
                .catch(function(err) {
                    console.error('Failed to remove participant:', err);
                    participantListState = previousState;
                    renderParticipantList(participantListState);
                    alert('Unable to remove participant right now.');
                });
        }

        // ============================================================
        // VIDEO SPOTLIGHT MODE
        // ============================================================
        let currentlySpotlightedContainer = null;

        function toggleZoom(container) {
            const grid = document.getElementById('gridContainer');
            if (!grid) return;

            if (currentlySpotlightedContainer && currentlySpotlightedContainer !== container) {
                currentlySpotlightedContainer.classList.remove('video-box-spotlight');
                currentlySpotlightedContainer.style.cursor = 'zoom-in';
            }

            if (container.classList.contains('video-box-spotlight')) {
                container.classList.remove('video-box-spotlight');
                container.style.cursor = 'zoom-in';
                grid.classList.remove('has-spotlight');
                currentlySpotlightedContainer = null;
                grid.scrollTop = 0;
            } else {
                container.classList.add('video-box-spotlight');
                container.style.cursor = 'zoom-out';
                grid.classList.add('has-spotlight');
                currentlySpotlightedContainer = container;

                // Scroll the spotlighted video into view smoothly
                container.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });
            }
        }

        // ============================================================
        // AVATAR PLACEHOLDER FOR CAMERA OFF STATE
        // ============================================================
        function updateAvatarPlaceholder(videoBox, p) {
            if (!videoBox) return;

            let placeholder = videoBox.querySelector('.avatar-placeholder');
            const videoEl = videoBox.querySelector('video');

            if (p.is_video_off) {
                // Camera is OFF: hide video and display avatar/initials
                if (videoEl) {
                    videoEl.style.opacity = '0';
                    videoEl.style.visibility = 'hidden';
                }

                if (!placeholder) {
                    placeholder = document.createElement('div');
                    placeholder.className = 'avatar-placeholder absolute inset-0 flex items-center justify-center bg-slate-950/90 z-10';

                    const avatarContainer = document.createElement('div');
                    avatarContainer.className = 'w-20 h-20 md:w-24 md:h-24 rounded-full flex items-center justify-center shadow-lg border-2 border-indigo-800 bg-indigo-600 text-white font-bold select-none overflow-hidden';

                    const avatarUrl = p.user_avatar;
                    if (avatarUrl) {
                        const img = document.createElement('img');
                        img.src = avatarUrl;
                        img.className = 'w-full h-full object-cover';
                        img.alt = p.user_name || 'User';
                        img.onerror = function() {
                            // Fallback to initial if image loading fails
                            avatarContainer.innerHTML = '';
                            const span = document.createElement('span');
                            span.className = 'text-3xl md:text-4xl font-semibold';
                            span.textContent = (p.user_name || 'U').charAt(0).toUpperCase();
                            avatarContainer.appendChild(span);
                        };
                        avatarContainer.appendChild(img);
                    } else {
                        const span = document.createElement('span');
                        span.className = 'text-3xl md:text-4xl font-semibold';
                        span.textContent = (p.user_name || 'U').charAt(0).toUpperCase();
                        avatarContainer.appendChild(span);
                    }

                    placeholder.appendChild(avatarContainer);
                    videoBox.appendChild(placeholder);
                }
            } else {
                // Camera is ON: show video and remove placeholder
                if (videoEl) {
                    videoEl.style.opacity = '1';
                    videoEl.style.visibility = 'visible';
                }
                if (placeholder) {
                    placeholder.remove();
                }
            }
        }

        // ============================================================
        // VIDEO GRID RENDERER
        // ============================================================
        function addVideo(peerId, stream, isLocal) {
            if (videoElements[peerId]) return;
            if (isEnded) return;

            const grid = document.getElementById('gridContainer');

            if (isLocal) {
                const localVideo = document.getElementById('localVideo');
                if (localVideo) {
                    localVideo.srcObject = stream;
                    localVideo.muted = true;
                    localVideo.play().catch(function() {});

                    const localContainer = localVideo.parentElement;
                    if (localContainer) {
                        localContainer.id = 'video-' + peerId;
                        videoElements[peerId] = localContainer;

                        // Attach toggle zoom listener to local video box
                        localContainer.style.cursor = 'zoom-in';
                        localContainer.addEventListener('click', function(e) {
                            toggleZoom(localContainer);
                        });

                        // Set initial placeholder state for local user
                        const videoTrack = stream.getVideoTracks()[0];
                        const isVideoOff = videoTrack ? !videoTrack.enabled : true;
                        updateAvatarPlaceholder(localContainer, {
                            is_video_off: isVideoOff,
                            user_name: myName,
                            user_avatar: myAvatar
                        });
                    }

                    const waiting = document.getElementById('waitingMessage');
                    if (waiting && grid.children.length > 1) waiting.style.display = 'none';
                    return;
                }
            }

            const name = participantNames[peerId] || 'User';
            const avatarUrl = participantAvatars[peerId] || null;

            const div = document.createElement('div');
            div.id = 'video-' + peerId;
            if (participantNames[peerId + '_userId']) {
                div.dataset.userId = participantNames[peerId + '_userId'];
            }
            div.className = 'relative w-full max-w-[360px] mx-auto bg-slate-900 rounded-xl overflow-hidden border border-slate-800 shadow-md aspect-video cursor-pointer';

            // Attach toggle zoom listener to remote video box
            div.style.cursor = 'zoom-in';
            div.addEventListener('click', function(e) {
                toggleZoom(div);
            });

            const video = document.createElement('video');
            video.autoplay = true;
            video.playsInline = true;
            video.muted = false;
            video.srcObject = stream;
            video.className = 'w-full h-full object-cover';

            const label = document.createElement('div');
            label.className = 'absolute bottom-3 left-3 bg-slate-950/80 backdrop-blur text-xs px-3 py-1.5 rounded-lg border border-slate-800 text-slate-200 font-medium shadow-md';
            label.textContent = name;

            div.appendChild(video);
            div.appendChild(label);
            grid.appendChild(div);

            video.play().catch(function(err) {
                console.warn('Video play failed:', err);
            });

            videoElements[peerId] = div;

            // Set initial placeholder state for remote user if state is known
            const pState = participantListState.find(p => p.peer_id === peerId);
            if (pState) {
                updateAvatarPlaceholder(div, pState);
            } else {
                updateAvatarPlaceholder(div, {
                    is_video_off: false,
                    user_name: name,
                    user_avatar: avatarUrl
                });
            }

            // Apply visual hand-raised badge if already raised
            const key = peerId;
            const userId = participantNames[peerId + '_userId'];
            const handRaised = !!(window.handRaiseStates[key] || (userId && window.handRaiseStates[userId]));
            if (handRaised) {
                globalRenderHandRaise(div, true);
            }

            const waiting = document.getElementById('waitingMessage');
            if (waiting && grid.children.length > 1) waiting.style.display = 'none';

            console.log('Added video for:', name);
            
            // Adjust layout for screenshare if needed
            adjustScreenShareLayout();

            // Recalculate dynamic polling interval
            adjustPollingInterval();
        }

        function removeVideo(peerId) {
            if (videoElements[peerId]) {
                const el = videoElements[peerId];

                // If removing a screenshare box, restore the nested camera box first to avoid losing it
                if (peerId.endsWith('-screen')) {
                    const cameraBox = el.querySelector('[id^="video-"]:not([id$="-screen"])');
                    if (cameraBox) {
                        cameraBox.classList.remove('camera-pip-circle');
                        const grid = document.getElementById('gridContainer');
                        if (grid) grid.appendChild(cameraBox);
                        console.log('Restored nested camera box before removing screenshare');
                    }
                }

                if (currentlySpotlightedContainer === el) {
                    const grid = document.getElementById('gridContainer');
                    if (grid) {
                        grid.classList.remove('has-spotlight');
                        grid.scrollTop = 0;
                    }
                    currentlySpotlightedContainer = null;
                }
                const localVideo = el.querySelector('#localVideo');
                if (localVideo) {
                    localVideo.srcObject = null;
                } else {
                    el.remove();
                }
                delete videoElements[peerId];
                
                // Adjust layout for screenshare if needed
                adjustScreenShareLayout();
            }

            if (peers[peerId]) {
                try {
                    peers[peerId].destroy();
                } catch (e) {}
                delete peers[peerId];
            }
            delete isConnecting[peerId];

            // Recalculate dynamic polling interval
            adjustPollingInterval();
        }

        function adjustScreenShareLayout() {
            const grid = document.getElementById('gridContainer');
            if (!grid) return;

            // Find any active screenshare container
            const screenBox = Array.from(grid.querySelectorAll('[id$="-screen"]'))[0];

            if (screenBox) {
                // 1. Make the screenshare box spotlighted automatically
                if (!screenBox.classList.contains('video-box-spotlight')) {
                    if (currentlySpotlightedContainer && currentlySpotlightedContainer !== screenBox) {
                        currentlySpotlightedContainer.classList.remove('video-box-spotlight');
                    }
                    screenBox.classList.add('video-box-spotlight');
                    grid.classList.add('has-spotlight');
                    currentlySpotlightedContainer = screenBox;
                }

                // 2. Find the owner's camera container
                const screenId = screenBox.id; // e.g., "video-[parentPeerId]-screen"
                const parentPeerId = screenId.replace('video-', '').replace('-screen', '');
                let cameraBox = document.getElementById('video-' + parentPeerId);
                
                if (!cameraBox && parentPeerId === myPeerId) {
                    cameraBox = document.getElementById('video-local');
                }

                if (cameraBox && cameraBox.parentElement !== screenBox) {
                    // Move cameraBox inside screenBox
                    screenBox.appendChild(cameraBox);
                    cameraBox.classList.add('camera-pip-circle');
                    console.log('Moved camera box', cameraBox.id, 'into PIP circle inside shared screen');
                }
            } else {
                // Restore any PIP circle camera box back to the main grid
                const pipBoxes = document.querySelectorAll('.camera-pip-circle');
                pipBoxes.forEach(box => {
                    box.classList.remove('camera-pip-circle');
                    grid.appendChild(box);
                    console.log('Restored camera box', box.id, 'to the grid container');
                });

                // If the screenshare spotlight was removed, clear spotlight state
                if (currentlySpotlightedContainer && currentlySpotlightedContainer.id && currentlySpotlightedContainer.id.endsWith('-screen')) {
                    currentlySpotlightedContainer.classList.remove('video-box-spotlight');
                    grid.classList.remove('has-spotlight');
                    currentlySpotlightedContainer = null;
                }
            }
        }

        // ============================================================
        // LOOPS
        // ============================================================
        function startAutoRefresh() {
            if (refreshInterval) clearInterval(refreshInterval);
            refreshInterval = setInterval(function() {
                if (!isEnded) getParticipants();
            }, 1000);
        }

        function startChatPolling() {
            if (chatPollingInterval) clearInterval(chatPollingInterval);
            pollChatMessages();
            chatPollingInterval = setInterval(function() {
                if (!isEnded) pollChatMessages();
            }, 1000);
        }

        function pollChatMessages() {
            if (isEnded) return;

            const url = '/meeting/chat/' + meetingId + (lastChatMessageId ? '?after_id=' + lastChatMessageId : '');

            fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(function(res) {
                    return res.json();
                })
                .then(function(messages) {
                    if (!Array.isArray(messages) || !messages.length) return;

                    messages.forEach(handleIncomingChatMessage);
                })
                .catch(function(err) {
                    console.error('Chat polling failed:', err);
                });
        }

        function startSignalPolling() {
            setInterval(function() {
                pollSignals();
            }, 1000);
        }

        // Add this to check Echo connection
        function debugEchoConnection() {
            console.log('ECHO DEBUG:');
            console.log('  Echo available:', !!window.Echo);
            console.log('  Meeting ID:', meetingId);
            console.log('  Auth User ID:', authUserId);

            if (window.Echo && window.Echo.connector) {
                console.log('  Connected:', window.Echo.connector.connected);
                console.log('  Channels:', Object.keys(window.Echo.connector.channels || {}));

                // Check if meeting channel exists
                const channelName = 'meeting.' + meetingId;
                const hasChannel = window.Echo.connector.channels &&
                    window.Echo.connector.channels[channelName];
                console.log('  Has channel "' + channelName + '":', !!hasChannel);
            }
        }
        // ============================================================
        // ECHO LISTENERS
        function initEchoListeners() {
            if (echoListenersInitialized) return;

            if (typeof window.Echo === 'undefined') {
                console.warn('Echo websocket listener not active');
                return;
            }

            if (!window.Echo.connector || window.Echo.connector.pusher?.connection?.state !== 'connected') {
                console.log('Waiting for Pusher connection...');
                setTimeout(initEchoListeners, 500);
                return;
            }

            echoListenersInitialized = true;
            console.log('Initializing Echo listeners for meeting:', meetingId);

            const meetingChannel = window.Echo.private('meeting.' + meetingId);
            activeMeetingChannel = meetingChannel;

            meetingChannel
                // HAND RAISE - Server Event
                .listen('.user-raised-hand', function(payload) {
                    console.log('HAND RAISE RECEIVED (server event):', payload);
                    console.log('Payload userId:', payload.userId);
                    console.log('Auth userId:', authUserId);
                    console.log('Is this my own hand raise?', payload.userId == authUserId);

                    if (payload.userId == authUserId) {
                        console.log('Skipping own hand raise');
                        return;
                    }

                    console.log('Proceeding to handle hand raise for other user');
                    handleIncomingHandRaise(payload);
                })
                // CHAT MESSAGE - Server Event
                .listen('.chat-message', function(payload) {
                    console.log('Chat message received (server):', payload);
                    handleIncomingChatMessage(payload);
                })
                // CHAT MESSAGES - Client Whisper
                .listenForWhisper('chat-message', function(payload) {
                    console.log('Chat message received (whisper):', payload);
                    handleIncomingChatMessage(payload);
                })
                // HAND RAISE - Client Whisper
                .listenForWhisper('hand-raised', function(payload) {
                    console.log('Hand raise received (whisper):', payload);
                    handleIncomingHandRaise(payload);
                })
                // MEDIA STATE CHANGED - Server Event (100% instant and reliable)
                .listen('.media-state-changed', function(payload) {
                    console.log('Media state changed server event received:', payload);
                    handleIncomingMediaStateChange({
                        peer_id: payload.peerId,
                        user_id: payload.userId,
                        is_audio_muted: payload.isAudioMuted,
                        is_video_off: payload.isVideoOff
                    });
                })
                // MEDIA STATE CHANGED - Client Whisper (fast fallback)
                .listenForWhisper('media-state-changed', function(payload) {
                    console.log('Media state changed whisper received:', payload);
                    handleIncomingMediaStateChange(payload);
                })
                // PARTICIPANT READY - Client Whisper
                .listenForWhisper('participant-ready', function(payload) {
                    console.log('Participant ready whisper received:', payload);
                    const pid = payload.peer_id;
                    if (pid && pid !== myPeerId) {
                        participantNames[pid] = payload.user_name || 'User';
                        participantNames[pid + '_userId'] = payload.user_id;

                        // Check if we should initiate the connection
                        const shouldIInitiate = myPeerId > pid;
                        if (shouldIInitiate && !peers[pid] && !isConnecting[pid]) {
                            console.log('Outgoing connection to ready participant:', payload.user_name);
                            createPeerConnection(pid, true);
                        }
                    }
                })
                // WEBRTC SIGNAL - Client Whisper (Instant sub-second connection)
                .listenForWhisper('webrtc-signal', function(payload) {
                    if (payload.to_peer_id === myPeerId || payload.to_peer_id === myPeerId + '-screen') {
                        console.log('WebSocket WebRTC signal received from:', payload.from_peer_id);
                        processSignal(payload.from_peer_id, payload.signal_data);
                    }
                })
                // RECORDING REQUEST - Client Whisper
                .listenForWhisper('request-recording-permission', function(payload) {
                    if (isHost) {
                        console.log('WebSocket request recording permission:', payload);
                        showRecordingRequestModal(payload.requesterName, payload.requesterPeerId, payload.requesterUserId);
                    }
                })
                // RECORDING RESPONSE - Client Whisper
                .listenForWhisper('recording-permission-response', function(payload) {
                    if (payload.targetPeerId === myPeerId || (payload.targetUserId && payload.targetUserId == authUserId)) {
                        console.log('WebSocket recording permission response:', payload);
                        clearRecordingRequestTimer();

                        const recordBtn = document.getElementById('recordBtn');

                        if (payload.approved) {
                            recordingApprovedByHost = true;
                            resetRecordButton();
                            alert('Host approved your recording request. Starting recording now...');
                            startRecording();
                        } else {
                            recordingApprovedByHost = false;
                            recordingRejectedByHost = true;
                            resetRecordButton();
                            alert('Host rejected your recording request. You cannot request again.');
                        }
                    }
                })
                // PARTICIPANT JOINED - Now on public channel
                .listen('ParticipantJoined', function(data) {
                    console.log('Participant joined:', data);

                    const p = data.participant;
                    if (!p || p.user_id == authUserId || isEnded) {
                        console.log('Skipping join event');
                        return;
                    }

                    if (p.peer_id) {
                        participantNames[p.peer_id] = p.user_name || 'User';
                        participantNames[p.peer_id + '_userId'] = p.user_id;
                        participantAvatars[p.peer_id] = p.user_avatar || null;
                        console.log('Participant stored:', p.user_name, 'Peer:', p.peer_id);
                    }

                    // Clean up any old peer matching this user ID just in case
                    Object.keys(peers).forEach(function(pid) {
                        if (participantNames[pid + '_userId'] == p.user_id && pid !== p.peer_id) {
                            console.log('Destroying old peer connection for rejoining user:', pid);
                            removeVideo(pid);
                        }
                    });

                    runSync();
                })
                // MEETING ENDED - Now on public channel
                .listen('MeetingEnded', function(data) {
                    console.log('Meeting ended event received:', data);
                    endMeetingForAll(data.message || 'Meeting ended by host');
                })
                // MEETING ENDED (Whisper fallback) - Now on public channel
                .listenForWhisper('meeting-ended', function(data) {
                    console.log('Meeting ended whisper received:', data);
                    endMeetingForAll(data.message || 'Meeting ended by host');
                })
                // PARTICIPANT KICKED - Now on public channel
                .listenForWhisper('participant-kicked', function(data) {
                    console.log('Participant kicked:', data);

                    if (data && data.user_id == authUserId) {
                        endMeetingForAll(data.message || 'You have been removed from the meeting');
                    }
                })
                // USER LEFT - Now on public channel
                .listenForWhisper('user-left', function(data) {
                    console.log('User left:', data);

                    if (!data || data.user_id == authUserId) {
                        console.log('Skipping own leave event');
                        return;
                    }

                    // Clean up leaving user's connections immediately
                    Object.keys(peers).forEach(function(pid) {
                        if (participantNames[pid + '_userId'] == data.user_id) {
                            console.log('Cleaning up peer on user-left whisper:', pid);
                            removeVideo(pid);
                        }
                    });

                    runSync();
                });

            console.log('Echo listeners initialized (private meeting channel)');
        }

        function getMeetingChannel() {
            if (!window.Echo) return null;
            return window.Echo.private('meeting.' + meetingId);
        }

        // ============================================================
        // CHAT UI
        // ============================================================
        function appendMessage(senderName, message, isOwn) {
            const container = document.getElementById('chatMessages');
            if (!container || !message) return;

            const row = document.createElement('div');
            row.className = 'flex ' + (isOwn ? 'justify-end' : 'justify-start');

            const bubble = document.createElement('div');
            bubble.className = (isOwn ?
                'bg-blue-600 text-white' :
                'bg-[#111c31] text-slate-100 border border-slate-700') + ' rounded-xl px-3 py-2 max-w-[85%] break-words text-sm shadow-sm';

            if (!isOwn && senderName) {
                const nameEl = document.createElement('div');
                nameEl.className = 'text-[11px] font-semibold text-slate-400 mb-1';
                nameEl.textContent = senderName;
                bubble.appendChild(nameEl);
            }

            const textEl = document.createElement('div');
            textEl.textContent = message;
            bubble.appendChild(textEl);

            row.appendChild(bubble);
            container.appendChild(row);
            container.scrollTop = container.scrollHeight;
        }
        // ============================================================
        // MEETING CONTROLS
        // ============================================================
        function endMeeting() {
            if (!isHost) {
                alert('Only the host can end this meeting');
                return;
            }
            if (!confirm('End the meeting for all participants?')) return;

            if (window.Echo) {
                const meetingChannel = getMeetingChannel();
                if (meetingChannel && typeof meetingChannel.whisper === 'function') {
                    meetingChannel.whisper('meeting-ended', {
                        message: 'Meeting ended by host'
                    });
                }
            }

            closeAll();
            fetch('/meeting/end/' + meetingId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                keepalive: true
            }).catch(function(err) {
                console.error('End meeting request failed:', err);
            });
            window.location.href = '/dashboard';
        }

        function endMeetingForAll(message) {
            if (isEnded) return;
            isEnded = true;
            closeAll();
            window.location.href = '/dashboard';
        }

        function closeAll() {
            isEnded = true;
            if (syncInterval) clearInterval(syncInterval);
            if (recordingTimer) {
                clearInterval(recordingTimer);
                recordingTimer = null;
            }
            if (mediaRecorder && isRecording) {
                try {
                    mediaRecorder.stop();
                } catch (e) {}
            }
            if (recordingCaptureStream) {
                recordingCaptureStream.getTracks().forEach(track => track.stop());
                recordingCaptureStream = null;
            }
            if (recordingStream) {
                recordingStream.getTracks().forEach(track => track.stop());
                recordingStream = null;
            }
            if (screenStream) {
                screenStream.getTracks().forEach(track => track.stop());
                screenStream = null;
            }

            Object.keys(peers).forEach(function(peerId) {
                try {
                    peers[peerId].destroy();
                } catch (e) {}
            });
            peers = {};
            isConnecting = {};

            if (localStream) {
                localStream.getTracks().forEach(t => t.stop());
                localStream = null;
            }
            videoElements = {};
        }

        function leaveMeeting() {
            if (!confirm('Leave the meeting?')) return;

            if (window.Echo) {
                const meetingChannel = getMeetingChannel();
                if (meetingChannel && typeof meetingChannel.whisper === 'function') {
                    meetingChannel.whisper('user-left', {
                        user_id: authUserId,
                        user_name: document.getElementById('userName')?.value || 'User'
                    });
                }
            }

            closeAll();
            fetch('/meeting/leave', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                keepalive: true,
                body: JSON.stringify({
                    meeting_id: meetingId
                })
            }).catch(function(err) {
                console.error('Leave meeting request failed:', err);
            });
            window.location.href = '/dashboard';
        }


        function syncStatusToServer() {
            if (!localStream) return;
            const audioTrack = localStream.getAudioTracks()[0];
            const videoTrack = localStream.getVideoTracks()[0];
            const isMuted = audioTrack ? !audioTrack.enabled : true;
            const isVideoOff = videoTrack ? !videoTrack.enabled : true;

            const mediaPayload = {
                type: 'media_state_changed',
                peer_id: myPeerId,
                user_id: authUserId,
                is_audio_muted: isMuted,
                is_video_off: isVideoOff
            };

            // 1. Broadcast media status changes instantly via direct WebRTC Data Channel
            broadcastToConnectedPeers(mediaPayload);

            // 2. Broadcast media status changes instantly via WebSocket whisper (fallback)
            if (activeMeetingChannel && typeof activeMeetingChannel.whisper === 'function') {
                activeMeetingChannel.whisper('media-state-changed', mediaPayload);
            }

            fetch('/meeting/update-status/' + meetingId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    is_audio_muted: isMuted,
                    is_video_off: isVideoOff
                })
            }).catch(err => console.error('Status sync failed:', err));
        }

        // ============================================================
        // MEDIA TOGGLES
        // ============================================================
        function toggleAudio() {
            if (!localStream) return;
            const track = localStream.getAudioTracks()[0];
            if (track) {
                track.enabled = !track.enabled;
                const btn = document.getElementById('toggleAudioBtn');
                const micOnIcon = document.getElementById('micOnIcon');
                const micOffIcon = document.getElementById('micOffIcon');
                if (track.enabled) {
                    if (micOnIcon) micOnIcon.classList.remove('hidden');
                    if (micOffIcon) micOffIcon.classList.add('hidden');
                    btn.className = 'bg-black hover:bg-zinc-900 border-2 border-zinc-700 text-white p-3 rounded-full w-14 h-14 flex items-center justify-center transition active:scale-95 shadow-md';
                } else {
                    if (micOnIcon) micOnIcon.classList.add('hidden');
                    if (micOffIcon) micOffIcon.classList.remove('hidden');
                    btn.className = 'bg-black hover:bg-zinc-900 border-2 border-zinc-700 text-white p-3 rounded-full w-14 h-14 flex items-center justify-center transition active:scale-95 shadow-md';
                }
                syncStatusToServer();

                // Immediate feedback for local mute badge
                const boxId = myPeerId ? 'video-' + myPeerId : 'video-local';
                const videoBox = document.getElementById(boxId);
                if (videoBox) {
                    let badge = videoBox.querySelector('.mute-indicator');
                    if (!track.enabled) { // muted
                        if (!badge) {
                            badge = document.createElement('span');
                            badge.className = 'mute-indicator';
                            badge.style.cssText = 'position: absolute; top: 12px; right: 12px; width: 32px; height: 32px; border-radius: 50%; background-color: #ffffff; border: 1.5px solid #d1d5db; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); z-index: 20;';
                            badge.innerHTML = `
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2.5" xmlns="http://www.w3.org/2000/svg">
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                    <path d="M9 9v3a3 3 0 0 0 5.12 2.12M15 9.34V4a3 3 0 0 0-5.94-.6"></path>
                                    <path d="M17 16.95A7 7 0 0 1 5 12v-2m14 0v2a7 7 0 0 1-.11 1.23"></path>
                                    <line x1="12" y1="19" x2="12" y2="23"></line>
                                    <line x1="8" y1="23" x2="16" y2="23"></line>
                                </svg>
                            `;
                            videoBox.appendChild(badge);
                        }
                    } else if (badge) {
                        badge.remove();
                    }
                }
            }
        }

        function toggleVideo() {
            if (!localStream) return;
            const track = localStream.getVideoTracks()[0];
            if (track) {
                track.enabled = !track.enabled;
                const btn = document.getElementById('toggleVideoBtn');
                const camOnIcon = document.getElementById('camOnIcon');
                const camOffIcon = document.getElementById('camOffIcon');
                if (track.enabled) {
                    if (camOnIcon) camOnIcon.classList.remove('hidden');
                    if (camOffIcon) camOffIcon.classList.add('hidden');
                    btn.className = 'bg-black hover:bg-zinc-900 border-2 border-zinc-700 text-white p-3 rounded-full w-14 h-14 flex items-center justify-center transition-colors shadow-md';
                } else {
                    if (camOnIcon) camOnIcon.classList.add('hidden');
                    if (camOffIcon) camOffIcon.classList.remove('hidden');
                    btn.className = 'bg-black hover:bg-zinc-900 border-2 border-zinc-700 text-white p-3 rounded-full w-14 h-14 flex items-center justify-center transition-colors shadow-md';
                }
                syncStatusToServer();

                // Immediate feedback for local video box placeholder
                const localBox = document.getElementById('localVideo')?.parentElement;
                if (localBox) {
                    updateAvatarPlaceholder(localBox, {
                        is_video_off: !track.enabled,
                        user_name: myName,
                        user_avatar: myAvatar
                    });
                }
            }
        }

        // ============================================================
        // SCREEN SHARING
        // ============================================================
        function findNativePeerConnection(peerObj) {
            if (!peerObj) return null;
            if (typeof peerObj.getSenders === 'function') return peerObj;
            if (peerObj.pc && typeof peerObj.pc.getSenders === 'function') return peerObj.pc;
            if (peerObj._pc && typeof peerObj._pc.getSenders === 'function') return peerObj._pc;
            if (peerObj.peerConnection && typeof peerObj.peerConnection.getSenders === 'function') return peerObj.peerConnection;
            return null;
        }

        async function toggleScreenShare() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getDisplayMedia) {
                alert("Screen sharing not supported.");
                return;
            }

            const screenBtn = document.getElementById('screenShareBtn');
            if (!screenBtn) return;

            if (!isScreenSharing) {
                try {
                    screenStream = await navigator.mediaDevices.getDisplayMedia({
                        video: true,
                        audio: false
                    });

                    const screenPeerId = myPeerId + '-screen';
                    participantNames[screenPeerId] = myName + " (Shared Screen)";

                    // Add local video element for our screen
                    addVideo(screenPeerId, screenStream, false);

                    // Create connections for screenshare to all other peers
                    Object.keys(peers).forEach(peerId => {
                        if (peerId.endsWith('-screen')) return;
                        createPeerConnection(peerId + '-screen', true, screenStream);
                    });

                    const screenTrack = screenStream.getVideoTracks()[0];
                    screenTrack.onended = () => stopScreenShare();

                    screenBtn.className = 'bg-blue-600 hover:bg-blue-500 border-2 border-blue-800 text-white p-3 rounded-full w-14 h-14 flex items-center justify-center transition active:scale-95 shadow-md';
                    isScreenSharing = true;

                } catch (err) {
                    console.error("Screen Share failed:", err);
                    alert("Screen Share Failed: " + err.message);
                }
            } else {
                stopScreenShare();
            }
        }

        function stopScreenShare() {
            const screenBtn = document.getElementById('screenShareBtn');

            if (screenStream) {
                screenStream.getTracks().forEach(track => track.stop());
                screenStream = null;
            }

            // Remove local screenshare box
            const screenPeerId = myPeerId + '-screen';
            removeVideo(screenPeerId);

            // Close and delete all screenshare connections
            Object.keys(peers).forEach(peerId => {
                if (peerId.endsWith('-screen')) {
                    removeVideo(peerId);
                }
            });

            if (screenBtn) {
                screenBtn.className = 'bg-black hover:bg-zinc-900 border-2 border-zinc-700 text-white p-3 rounded-full w-14 h-14 flex items-center justify-center transition active:scale-95 shadow-md';
            }
            isScreenSharing = false;
        }

        // ============================================================
        // CHAT
        function sendChatMessage() {
            const input = document.getElementById('chatInput');
            const text = input.value.trim();
            if (!text) return;

            const senderName = document.getElementById('userName').value || 'You';
            appendMessage('You', text, true);
            input.value = '';

            // Add to sender's own recent messages cache to avoid duplicate displays
            recentMessages.push({
                userId: authUserId,
                message: text,
                time: Date.now()
            });

            // 1. Broadcast via WebRTC data channel (sub-second)
            broadcastToConnectedPeers({
                type: 'chat_message',
                user_id: authUserId,
                sender_name: senderName,
                message: text,
                timestamp: new Date().toISOString()
            });

            // 2. Broadcast via Echo Whisper (sub-second)
            if (window.Echo) {
                const meetingChannel = getMeetingChannel();
                if (meetingChannel && typeof meetingChannel.whisper === 'function') {
                    meetingChannel.whisper('chat-message', {
                        user_id: authUserId,
                        sender_name: senderName,
                        message: text,
                        timestamp: new Date().toISOString()
                    });
                }
            }

            // 3. Persist and broadcast via Server (reliable fallback)
            sendChatViaServerEvent(text, senderName);
        }

        // ============================================================
        // CHAT VIA SERVER EVENT (Reliable)
        // ============================================================
        function sendChatViaServerEvent(message, senderName) {
            fetch('/meeting/chat/broadcast', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        meeting_id: meetingId,
                        message: message,
                        sender_name: senderName,
                        user_id: authUserId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Chat sent via server event:', data);
                    if (data && data.message_id) {
                        receivedChatIds.add(data.message_id);
                        lastChatMessageId = Math.max(lastChatMessageId, data.message_id);
                    }
                })
                .catch(err => console.error('Server event failed:', err));
        }

        // ============================================================
        // RENDER HAND RAISE BADGE - GLOBAL
        // ============================================================
        function globalRenderHandRaise(containerElement, shouldRaise) {
            if (!containerElement) {
                console.warn('No container provided for hand raise');
                return;
            }

            // If video element passed, get its parent
            if (containerElement.tagName && containerElement.tagName.toLowerCase() === 'video') {
                containerElement = containerElement.parentElement;
            }

            if (!containerElement) {
                console.warn('Could not find parent container for video');
                return;
            }

            // Make sure container has position relative
            containerElement.style.position = 'relative';

            // Remove existing badge
            let existingBadge = containerElement.querySelector('.hand-raise-badge');
            if (existingBadge) {
                existingBadge.remove();
                console.log('Removed existing badge');
            }

            // Remove existing styles
            containerElement.classList.remove('ring-4', 'ring-amber-500', 'border-amber-500');
            containerElement.style.outline = 'none';
            containerElement.style.outlineOffset = '0';

            if (shouldRaise) {
                // Add visual indicators
                containerElement.classList.add('ring-4', 'ring-amber-500', 'border-amber-500');
                containerElement.style.outline = '4px solid #f59e0b';
                containerElement.style.outlineOffset = '-2px';

                // Create and add badge
                const badge = document.createElement('div');
                badge.className = 'hand-raise-badge';
                badge.textContent = 'Raised';
                badge.style.cssText = `
                    position: absolute;
                    top: 12px;
                    right: 12px;
                    background: #f59e0b;
                    color: #020617;
                    padding: 6px 14px;
                    border-radius: 20px;
                    font-weight: bold;
                    font-size: 12px;
                    z-index: 9999;
                    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.5);
                    animation: pulse 1s ease-in-out infinite;
                    pointer-events: none;
                `;
                containerElement.appendChild(badge);
                console.log('Hand raise badge rendered');
            } else {
                console.log('Hand raise badge removed');
            }
        }

        // ============================================================
        // HAND RAISE - IMPROVED VERSION
        // ============================================================
        function handleIncomingHandRaise(payload) {
            console.log("HAND RAISE RECEIVED:", payload);
            if (!payload) return;

            const userId = payload.userId !== undefined ? payload.userId : payload.user_id;
            const peerId = payload.peerId !== undefined ? payload.peerId : payload.peer_id;
            const userName = payload.userName !== undefined ? payload.userName : payload.user_name;
            const raised = payload.raised !== undefined ? payload.raised : payload.hand_raised;

            if (userId == authUserId) {
                console.log('Skipping own hand raise');
                return;
            }

            // Save the timestamp to prevent sync poll race condition
            if (!window.lastRealtimeHandRaiseTime) window.lastRealtimeHandRaiseTime = {};
            window.lastRealtimeHandRaiseTime[userId] = Date.now();

            // Store the state
            const key = peerId || String(userId);
            window.handRaiseStates[key] = !!raised;

            participantListState = participantListState.map(function(participant) {
                if (participant.user_id == userId) {
                    participant.hand_raised = !!raised;
                }
                return participant;
            });
            renderParticipantList(participantListState);

            let remoteBox = null;

            // Method 1: Try by peerId
            if (peerId) {
                remoteBox = document.getElementById('video-' + peerId);
                if (remoteBox) {
                    console.log('Found container by peerId:', peerId);
                }
            }

            // Method 2: Search by userId in data attribute
            if (!remoteBox && userId) {
                const allVideos = document.querySelectorAll('[id^="video-"]');
                for (const box of allVideos) {
                    if (box.dataset && box.dataset.userId == userId) {
                        remoteBox = box;
                        console.log('Found container by userId:', userId);
                        break;
                    }
                }
            }

            // Method 3: Search by label text
            if (!remoteBox && userName) {
                const allVideos = document.querySelectorAll('[id^="video-"]');
                for (const box of allVideos) {
                    const label = box.querySelector('.video-label');
                    if (label && label.textContent.includes(userName)) {
                        remoteBox = box;
                        console.log('Found container by userName:', userName);
                        break;
                    }
                }
            }

            if (remoteBox) {
                console.log('Rendering hand raise badge');
                globalRenderHandRaise(remoteBox, !!raised);
            } else {
                console.warn('Could not find container for peer:', peerId || userId);

                // Retry after delay
                setTimeout(() => {
                    let retryBox = null;
                    if (peerId) {
                        retryBox = document.getElementById('video-' + peerId);
                    }
                    if (!retryBox && userId) {
                        const allVideos = document.querySelectorAll('[id^="video-"]');
                        for (const box of allVideos) {
                            if (box.dataset && box.dataset.userId == userId) {
                                retryBox = box;
                                break;
                            }
                        }
                    }
                    if (retryBox) {
                        console.log('Found container after delay');
                        globalRenderHandRaise(retryBox, !!raised);
                    }
                }, 2000);
            }
        }

        // ============================================================
        // RECORDING
        // ============================================================
        // ============================================================
        // RECORDING
        // ============================================================
        function getHostPeerId() {
            if (isHost) return myPeerId;
            const hostParticipant = participantListState.find(p => p.is_host);
            return hostParticipant ? hostParticipant.peer_id : null;
        }

        function requestRecordingPermission() {
            if (recordingRejectedByHost) {
                alert('Your previous recording request was rejected by the host. You cannot request again.');
                return;
            }

            const hostPeerId = getHostPeerId();
            if (!hostPeerId) {
                alert('Host is not present in the meeting. Cannot request recording permission.');
                return;
            }

            const recordBtn = document.getElementById('recordBtn');
            if (recordBtn) {
                recordBtn.innerHTML = 'Requesting...';
                recordBtn.disabled = true;
            }

            const payload = {
                type: 'request_recording_permission',
                requesterPeerId: myPeerId,
                requesterName: myName,
                requesterUserId: authUserId,
                targetPeerId: hostPeerId
            };

            // 1. Send via WebSocket Whisper (instant realtime)
            if (activeMeetingChannel && typeof activeMeetingChannel.whisper === 'function') {
                activeMeetingChannel.whisper('request-recording-permission', payload);
                console.log('Record request sent via WebSocket Whisper');
            }

            // 2. DB fallback
            sendSignal(hostPeerId, payload);

            // Start 10s timer
            clearRecordingRequestTimer();
            recordingRequestTimer = setTimeout(() => {
                console.log('Recording request timed out (no host response after 10s)');
                recordingRequestTimer = null;
                if (!isRecording && !recordingApprovedByHost && !recordingRejectedByHost) {
                    resetRecordButton();
                    alert('No response from host. You can try requesting again.');
                }
            }, 10000);
        }

        function playNotificationSound() {
            try {
                const ctx = new(window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.type = 'sine';
                osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5 note
                gain.gain.setValueAtTime(0.12, ctx.currentTime);
                osc.start();
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
                osc.stop(ctx.currentTime + 0.35);
            } catch (e) {
                console.warn('AudioContext failed:', e);
            }
        }

        function showRecordingRequestModal(requesterName, requesterPeerId, requesterUserId) {
            console.log('RECORDING REQUEST SHOW MODAL:', requesterName, requesterPeerId, requesterUserId);
            const existing = document.getElementById('recordingRequestModal');
            if (existing) existing.remove();

            // Play notification sound
            playNotificationSound();

            const modal = document.createElement('div');
            modal.id = 'recordingRequestModal';
            modal.style.cssText = `
                position: fixed;
                top: 96px;
                left: 50%;
                transform: translate(-50%, -20px);
                background: rgba(15, 23, 42, 0.95);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border: 1px solid rgba(51, 65, 85, 0.8);
                padding: 20px;
                border-radius: 16px;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
                z-index: 9999;
                max-width: 380px;
                width: calc(100% - 32px);
                transition: opacity 0.3s ease, transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
                opacity: 0;
                overflow: hidden;
            `;
            modal.innerHTML = `
                <div style="height: 4px; background: linear-gradient(90deg, #f43f5e, #ec4899, #6366f1); position: absolute; top: 0; left: 0; right: 0;"></div>
                <div style="display: flex; align-items: start; gap: 16px; margin-top: 4px;">
                    <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(244, 63, 94, 0.1); border: 1px solid rgba(244, 63, 94, 0.2); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg style="width: 20px; height: 20px; color: #f43f5e; animation: pulse 1.5s infinite;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="9" />
                            <circle cx="12" cy="12" r="4" fill="currentColor" />
                        </svg>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <h4 style="font-weight: 700; color: #ffffff; font-size: 14px; margin: 0; letter-spacing: 0.5px; font-family: sans-serif;">Recording Request</h4>
                        <p style="color: #cbd5e1; font-size: 12px; margin: 6px 0 0 0; line-height: 1.5; font-family: sans-serif;">
                            <span style="color: #ffffff; font-weight: 600;">${requesterName}</span> wants permission to record this meeting.
                        </p>
                        <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 16px;">
                            <button id="rejectRecordBtn" style="padding: 8px 16px; background: #0f172a; color: #e2e8f0; border: 1px solid rgba(51, 65, 85, 0.9); border-radius: 12px; font-size: 12px; font-weight: 600; cursor: pointer; transition: background 0.2s, transform 0.1s, box-shadow 0.2s; box-shadow: 0 10px 15px -3px rgba(15, 23, 42, 0.25);" onmouseover="this.style.background='#1e293b'" onmouseout="this.style.background='#0f172a'" onmousedown="this.style.transform='scale(0.95)'" onmouseup="this.style.transform='scale(1)'">
                                Reject
                            </button>
                            <button id="allowRecordBtn" style="padding: 8px 16px; background: #4f46e5; color: #ffffff; border: 1px solid rgba(79, 70, 229, 0.5); border-radius: 12px; font-size: 12px; font-weight: 600; cursor: pointer; box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.25); transition: background 0.2s, transform 0.1s;" onmouseover="this.style.background='#6366f1'" onmouseout="this.style.background='#4f46e5'" onmousedown="this.style.transform='scale(0.95)'" onmouseup="this.style.transform='scale(1)'">
                                Allow
                            </button>
                        </div>
                    </div>
                </div>
            `;
            const container = document.getElementById('meetingRoot') || document.body;
            container.appendChild(modal);

            requestAnimationFrame(() => {
                modal.style.opacity = '1';
                modal.style.transform = 'translate(-50%, 0)';
            });

            modal.querySelector('#allowRecordBtn').onclick = () => {
                const responsePayload = {
                    type: 'recording_permission_response',
                    approved: true,
                    targetPeerId: requesterPeerId,
                    targetUserId: requesterUserId || null
                };

                // 1. Send via WebSocket Whisper (instant realtime)
                if (activeMeetingChannel && typeof activeMeetingChannel.whisper === 'function') {
                    activeMeetingChannel.whisper('recording-permission-response', responsePayload);
                    console.log('Record approval sent via WebSocket Whisper');
                }

                // 2. DB fallback
                sendSignal(requesterPeerId, responsePayload);
                modal.style.opacity = '0';
                modal.style.transform = 'translate(-50%, -20px)';
                setTimeout(() => modal.remove(), 300);
            };

            modal.querySelector('#rejectRecordBtn').onclick = () => {
                const responsePayload = {
                    type: 'recording_permission_response',
                    approved: false,
                    targetPeerId: requesterPeerId,
                    targetUserId: requesterUserId || null
                };

                // 1. Send via WebSocket Whisper (instant realtime)
                if (activeMeetingChannel && typeof activeMeetingChannel.whisper === 'function') {
                    activeMeetingChannel.whisper('recording-permission-response', responsePayload);
                    console.log('Record rejection sent via WebSocket Whisper');
                }

                // 2. DB fallback
                sendSignal(requesterPeerId, responsePayload);
                modal.style.opacity = '0';
                modal.style.transform = 'translate(-50%, -20px)';
                setTimeout(() => modal.remove(), 300);
            };
        }

        function uploadRecording(blob) {
            const overlay = document.createElement('div');
            overlay.id = 'uploadProgressModal';
            overlay.style.cssText = `
                position: fixed;
                bottom: 24px;
                left: 24px;
                background: rgba(15, 23, 42, 0.95);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border: 1px solid rgba(51, 65, 85, 0.8);
                padding: 16px;
                border-radius: 16px;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
                z-index: 9999;
                max-width: 320px;
                width: calc(100% - 48px);
                transition: opacity 0.3s ease, transform 0.3s ease;
                opacity: 0;
                transform: translateY(20px);
                overflow: hidden;
            `;
            overlay.innerHTML = `
                <div style="height: 4px; background: linear-gradient(90deg, #6366f1, #8b5cf6, #3b82f6); position: absolute; top: 0; left: 0; right: 0;"></div>
                <div style="display: flex; align-items: start; gap: 14px; margin-top: 4px;">
                    <div style="width: 32px; height: 32px; border-radius: 50%; border: 3px solid rgba(99, 102, 241, 0.15); border-top-color: #6366f1; animation: spin 1s linear infinite; flex-shrink: 0; margin-top: 2px;"></div>
                    <div style="flex: 1; min-width: 0;">
                        <h3 style="font-weight: 700; color: #ffffff; font-size: 13px; margin: 0; letter-spacing: 0.5px; font-family: sans-serif;">Saving Recording</h3>
                        <p style="color: #94a3b8; font-size: 11px; margin: 4px 0 8px 0; line-height: 1.4; font-family: sans-serif;">Uploading meeting recording to the server...</p>
                        <div style="width: 100%; background: #1e293b; border-radius: 9999px; height: 6px; overflow: hidden; margin-bottom: 4px;">
                            <div id="uploadProgressBar" style="background: linear-gradient(90deg, #6366f1, #3b82f6); height: 100%; border-radius: 9999px; width: 0%; transition: width 0.3s ease;"></div>
                        </div>
                        <span id="uploadProgressText" style="font-size: 10px; font-family: monospace; color: #94a3b8; font-weight: 600;">0%</span>
                    </div>
                </div>
            `;
            const container = document.getElementById('meetingRoot') || document.body;
            container.appendChild(overlay);

            requestAnimationFrame(() => {
                overlay.style.opacity = '1';
                overlay.style.transform = 'translateY(0)';
            });

            const recordBtn = document.getElementById('recordBtn');
            if (recordBtn) recordBtn.disabled = true;

            const formData = new FormData();
            formData.append('video', blob, `recording_${meetingId}_${Date.now()}.webm`);
            formData.append('meeting_id', meetingId);

            const xhr = new XMLHttpRequest();
            xhr.open('POST', '/meeting/recording/upload', true);
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);

            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    const bar = document.getElementById('uploadProgressBar');
                    const text = document.getElementById('uploadProgressText');
                    if (bar) bar.style.width = percent + '%';
                    if (text) text.textContent = percent + '%';
                }
            };

            xhr.onload = function() {
                if (overlay) overlay.remove();
                resetRecordButton();

                if (xhr.status === 200) {
                    alert('Recording uploaded and saved to server successfully!');
                } else {
                    let errMsg = 'Unknown error';
                    try {
                        const resp = JSON.parse(xhr.responseText);
                        errMsg = resp.error || errMsg;
                    } catch (e) {}
                    alert('Failed to save recording on server: ' + errMsg);
                }
            };

            xhr.onerror = function() {
                if (overlay) overlay.remove();
                resetRecordButton();
                alert('A network error occurred during recording upload.');
            };

            xhr.send(formData);
        }

        async function startRecording() {
            if (isRecording) return;

            try {
                let captureStream = null;

                if (screenStream && screenStream.getVideoTracks().length) {
                    captureStream = screenStream;
                } else if (navigator.mediaDevices && navigator.mediaDevices.getDisplayMedia) {
                    captureStream = await navigator.mediaDevices.getDisplayMedia({
                        video: {
                            displaySurface: 'browser',
                            frameRate: 30
                        },
                        audio: true
                    });
                }

                if (!captureStream || !captureStream.getVideoTracks().length) {
                    alert('Please choose a browser tab or screen to record.');
                    resetRecordButton();
                    return;
                }

                recordingCaptureStream = captureStream;
                recordingStream = new MediaStream();

                const videoTrack = captureStream.getVideoTracks()[0];
                if (videoTrack) recordingStream.addTrack(videoTrack.clone());

                const tabAudioTrack = captureStream.getAudioTracks()[0];
                if (tabAudioTrack) {
                    recordingStream.addTrack(tabAudioTrack.clone());
                } else if (localStream) {
                    const micAudioTrack = localStream.getAudioTracks()[0];
                    if (micAudioTrack) recordingStream.addTrack(micAudioTrack.clone());
                }

                mediaRecorder = new MediaRecorder(recordingStream, {
                    mimeType: 'video/webm;codecs=vp9,opus',
                    videoBitsPerSecond: 2500000
                });

                recordedChunks = [];

                mediaRecorder.ondataavailable = function(event) {
                    if (event.data.size > 0) recordedChunks.push(event.data);
                };

                mediaRecorder.onstop = function() {
                    if (recordedChunks.length === 0) {
                        alert('No recording data available');
                        return;
                    }
                    const blob = new Blob(recordedChunks, {
                        type: 'video/webm'
                    });

                    uploadRecording(blob);
                };

                mediaRecorder.start(1000);
                isRecording = true;

                const recordBtn = document.getElementById('recordBtn');
                if (recordBtn) {
                    recordBtn.className = 'bg-red-600 text-white hover:bg-red-700 border-2 border-red-800 p-3 rounded-full w-14 h-14 flex items-center justify-center transition active:scale-95 shadow-md animate-pulse';
                }

                document.getElementById('recordingStatus').classList.remove('hidden');
                recordingSeconds = 0;
                if (recordingTimer) clearInterval(recordingTimer);
                recordingTimer = setInterval(() => {
                    recordingSeconds++;
                    const mins = String(Math.floor(recordingSeconds / 60)).padStart(2, '0');
                    const secs = String(recordingSeconds % 60).padStart(2, '0');
                    document.getElementById('recordingTimer').textContent = `${mins}:${secs}`;
                }, 1000);

                console.log('Recording started from browser tab');

            } catch (error) {
                console.error('Failed to start recording:', error);
                alert('Failed to start recording: ' + error.message);
                resetRecordButton();
            }
        }

        function stopRecording() {
            if (mediaRecorder && isRecording) {
                mediaRecorder.stop();
                isRecording = false;

                resetRecordButton();

                document.getElementById('recordingStatus').classList.add('hidden');
                if (recordingTimer) {
                    clearInterval(recordingTimer);
                    recordingTimer = null;
                }

                if (recordingCaptureStream) {
                    recordingCaptureStream.getTracks().forEach(track => track.stop());
                    recordingCaptureStream = null;
                }

                if (recordingStream) {
                    recordingStream.getTracks().forEach(track => track.stop());
                    recordingStream = null;
                }

                console.log('Recording stopped');
            }
        }

        function toggleRecording() {
            if (recordingRejectedByHost) {
                alert('Your previous recording request was rejected by the host. You cannot request again.');
                return;
            }
            if (isRecording) {
                stopRecording();
            } else {
                if (isHost || recordingApprovedByHost) {
                    startRecording();
                } else {
                    requestRecordingPermission();
                }
            }
        }

        // ============================================================
        // TIMER
        // ============================================================
        function startMeetingTimer() {
            const timerElement = document.getElementById('meetingTimer');
            if (!timerElement) return;

            function updateClock() {
                const now = new Date();
                const hrs = String(now.getHours()).padStart(2, '0');
                const mins = String(now.getMinutes()).padStart(2, '0');
                timerElement.textContent = `${hrs}:${mins}`;
            }
            setInterval(updateClock, 1000);
            updateClock();
        }

        // ============================================================
        // DOMContentLoaded - Initialize Everything
        // ============================================================
        document.addEventListener('DOMContentLoaded', function() {
            console.log('UI Framework Ready');

            // Button listeners
            document.getElementById('leaveMeetingBtn').addEventListener('click', leaveMeeting);
            document.getElementById('toggleAudioBtn').addEventListener('click', toggleAudio);
            document.getElementById('toggleVideoBtn').addEventListener('click', toggleVideo);

            const screenBtn = document.getElementById('screenShareBtn');
            if (screenBtn) screenBtn.addEventListener('click', toggleScreenShare);

            const sendBtn = document.getElementById('sendChatBtn');
            if (sendBtn) sendBtn.addEventListener('click', sendChatMessage);

            const chatInput = document.getElementById('chatInput');
            if (chatInput) {
                chatInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        sendChatMessage();
                    }
                });
            }

            const endBtn = document.getElementById('endMeetingBtn');
            if (endBtn) endBtn.addEventListener('click', endMeeting);

            const recordBtn = document.getElementById('recordBtn');
            if (recordBtn) recordBtn.addEventListener('click', toggleRecording);

            const participantCountBtn = document.getElementById('participantCountBtn');
            if (participantCountBtn) {
                participantCountBtn.addEventListener('click', function() {
                    toggleSidebarView('participants');
                });
            }

            const toggleChatBtn = document.getElementById('toggleChatBtn');
            if (toggleChatBtn) {
                toggleChatBtn.addEventListener('click', function() {
                    toggleSidebarView('chat');
                });
            }

            // Start timer
            startMeetingTimer();
            initEchoListeners();

            // ============================================================
            // HAND RAISE - Click Handler (Server Event)
            // ============================================================
            document.addEventListener('click', function(e) {
                const raiseHandBtn = e.target.closest('#raiseHandBtn');
                if (!raiseHandBtn) return;
                e.preventDefault();

                const newRaisedState = !window.isHandRaised;
                window.isHandRaised = newRaisedState;

                // Save local timestamp to prevent sync poll race condition
                if (!window.lastRealtimeHandRaiseTime) window.lastRealtimeHandRaiseTime = {};
                window.lastRealtimeHandRaiseTime[authUserId] = Date.now();

                console.log('Hand raise toggled:', newRaisedState ? 'RAISED' : 'LOWERED');

                // Update UI immediately for self
                if (window.isHandRaised) {
                    raiseHandBtn.className = 'bg-indigo-600 text-white border-2 border-indigo-800 p-3 rounded-full shadow-lg w-14 h-14 flex items-center justify-center transition-colors';
                } else {
                    raiseHandBtn.className = 'bg-black hover:bg-zinc-900 border-2 border-zinc-700 text-white p-3 rounded-full w-14 h-14 flex items-center justify-center transition active:scale-95 shadow-md';
                }

                let localBox = document.getElementById('localVideo')?.parentElement;
                if (localBox) {
                    globalRenderHandRaise(localBox, window.isHandRaised);
                }

                const activePeerId = myPeerId;
                if (!activePeerId) {
                    console.warn('Peer ID not ready yet');
                    window.isHandRaised = !newRaisedState;
                    if (window.isHandRaised) {
                        raiseHandBtn.className = 'bg-indigo-600 text-white border-2 border-indigo-800 p-3 rounded-full shadow-lg w-14 h-14 flex items-center justify-center transition-colors';
                    } else {
                        raiseHandBtn.className = 'bg-black hover:bg-zinc-900 border-2 border-zinc-700 text-white p-3 rounded-full w-14 h-14 flex items-center justify-center transition active:scale-95 shadow-md';
                    }
                    const localBox = document.getElementById('localVideo')?.parentElement;
                    if (localBox) {
                        globalRenderHandRaise(localBox, window.isHandRaised);
                    }
                    alert('Please wait a moment for the connection to initialize, then try again.');
                    return;
                }

                fetch('/meeting/raise-hand-event', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            meetingId: meetingId,
                            userId: authUserId,
                            peerId: activePeerId,
                            raised: window.isHandRaised
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Hand raise sent via server event:', data);
                    })
                    .catch(err => console.error('Server event failed:', err));

                // 1. Broadcast via WebRTC data channel (sub-second)
                broadcastToConnectedPeers({
                    type: 'hand_raised',
                    user_id: authUserId,
                    peer_id: activePeerId,
                    user_name: document.getElementById('userName')?.value || 'User',
                    raised: window.isHandRaised
                });

                // 2. Broadcast via Echo Whisper (sub-second)
                if (window.Echo) {
                    const meetingChannel = getMeetingChannel();
                    if (meetingChannel && typeof meetingChannel.whisper === 'function') {
                        meetingChannel.whisper('hand-raised', {
                            userId: authUserId,
                            peerId: activePeerId,
                            userName: document.getElementById('userName')?.value || 'User',
                            raised: window.isHandRaised
                        });
                    }
                }
            });

            // Copy meeting link handler
            document.getElementById('copyLinkBtn')?.addEventListener('click', function() {
                const meetingLink = window.location.href;
                navigator.clipboard.writeText(meetingLink).then(function() {
                    showToastNotification('Meeting link copied to clipboard!');
                }).catch(function(err) {
                    console.error('Failed to copy link:', err);
                    // Fallback copy method
                    const tempInput = document.createElement('input');
                    tempInput.value = meetingLink;
                    document.body.appendChild(tempInput);
                    tempInput.select();
                    document.execCommand('copy');
                    document.body.removeChild(tempInput);
                    showToastNotification('Meeting link copied to clipboard!');
                });
            });

            function showToastNotification(message) {
                const toast = document.createElement('div');
                toast.textContent = message;
                toast.style.cssText = `
                    position: fixed;
                    bottom: 24px;
                    left: 50%;
                    transform: translateX(-50%);
                    background-color: #0f172a; /* Slate 900 */
                    border: 1px solid #1e293b; /* Slate 800 */
                    color: #ffffff;
                    padding: 10px 20px;
                    border-radius: 12px;
                    font-size: 14px;
                    font-weight: 600;
                    z-index: 100;
                    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
                    pointer-events: none;
                    animation: toast-anim 2.5s ease-in-out forwards;
                `;
                
                if (!document.getElementById('toast-keyframes')) {
                    const style = document.createElement('style');
                    style.id = 'toast-keyframes';
                    style.innerHTML = `
                        @keyframes toast-anim {
                            0% { transform: translate(-50%, 20px); opacity: 0; }
                            15% { transform: translate(-50%, 0); opacity: 1; }
                            85% { transform: translate(-50%, 0); opacity: 1; }
                            100% { transform: translate(-50%, -20px); opacity: 0; }
                        }
                    `;
                    document.head.appendChild(style);
                }
                
                document.body.appendChild(toast);
                setTimeout(() => {
                    toast.remove();
                }, 2500);
            }

            // Start camera and signaling registration in parallel for maximum connection speed
            startCamera();
            generatePeerId();
        });

        function leaveMeetingWithoutConfirm() {
            if (window.Echo) {
                const meetingChannel = getMeetingChannel();
                if (meetingChannel && typeof meetingChannel.whisper === 'function') {
                    meetingChannel.whisper('user-left', {
                        user_id: authUserId,
                        user_name: document.getElementById('userName')?.value || 'User'
                    });
                }
            }

            closeAll();
            fetch('/meeting/leave', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                keepalive: true,
                body: JSON.stringify({
                    meeting_id: meetingId
                })
            }).catch(function(err) {
                console.error('Leave meeting request failed:', err);
            });
        }

        // ============================================================
        // WINDOW EVENTS
        // ============================================================
        window.addEventListener('beforeunload', function() {
            if (syncInterval) clearInterval(syncInterval);
            if (isRecording) stopRecording();
            leaveMeetingWithoutConfirm();
        });
    </script>

    <style>
        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.05);
                opacity: 0.8;
            }
        }

        @keyframes slideDown {
            0% {
                transform: translate(-50%, -20px);
                opacity: 0;
            }

            100% {
                transform: translate(-50%, 0);
                opacity: 1;
            }
        }

        .animate-slide-down {
            animation: slideDown 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .video-box-spotlight {
            order: -1 !important;
            grid-column: 1 / -1 !important;
            width: 100% !important;
            max-width: none !important;
            height: auto !important;
            min-height: 240px !important;
            max-height: 65vh !important;
            aspect-ratio: 16/9 !important;
            border: 2px solid rgba(59, 130, 246, 0.8) !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        @media (min-width: 768px) {
            .video-box-spotlight {
                min-height: 450px !important;
            }
        }

        .video-box-spotlight video {
            object-fit: contain !important;
            background-color: #020617 !important;
        }

        #gridContainer.has-spotlight {
            align-items: start !important;
            grid-auto-rows: auto !important;
        }

        .camera-pip-circle {
            position: absolute !important;
            bottom: 16px !important;
            right: 16px !important;
            width: 120px !important;
            height: 120px !important;
            border-radius: 50% !important;
            border: 3px solid #ffffff !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5) !important;
            z-index: 50 !important;
            aspect-ratio: 1/1 !important;
            overflow: hidden !important;
            margin: 0 !important;
            max-width: none !important;
        }

        .camera-pip-circle video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            border-radius: 50% !important;
        }

        .camera-pip-circle > div,
        .camera-pip-circle > span {
            display: none !important;
        }

        .bg-slate-850 {
            background-color: #1e293b !important;
        }

        .hover\:bg-slate-800:hover {
            background-color: #0f172a !important;
        }
    </style>
</x-app-layout>