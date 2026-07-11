<x-app-layout>
    <div class="h-screen w-screen flex flex-col bg-[#030812] text-white overflow-hidden">

        <div class="bg-[#071120] px-4 md:px-6 py-3 flex justify-between items-center border-b border-slate-700/80 z-20 shadow-[0_6px_20px_rgba(2,8,23,0.35)]">
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-[15px] font-semibold leading-5 tracking-wide text-white select-none">
                    Meeting
                </h1>

                <span class="text-white text-xs flex items-center gap-2 h-10 px-3 rounded-lg border border-slate-600 bg-[#14233d] shadow-sm">
                    Code: <span class="text-white font-mono bg-[#1d2f4a] px-2 py-1 rounded border border-slate-600 select-all">{{ $meeting->room_id ?? 'N/A' }}</span>
                </span>

                <button id="participantCountBtn" type="button" class="h-10 px-3 rounded-lg border border-slate-600 bg-[#14233d] text-white text-xs font-semibold hover:bg-[#1a2d49] hover:text-white transition cursor-pointer shadow-sm">
                    <span id="participantCount">1 participant</span>
                </button>

                <div class="flex items-center gap-2 h-10 px-3 rounded-lg border border-slate-600 bg-[#14233d] shadow-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span id="meetingTimer" class="text-xs font-mono text-white font-semibold tracking-wider">00:00:00</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button id="copyLinkBtn" class="h-10 px-4 rounded-lg border border-blue-400 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold transition active:scale-95 shadow-sm">
                    Copy Link
                </button>
                @if(Auth::id() == $meeting->created_by)
                <button id="endMeetingBtn" class="h-10 px-4 rounded-lg bg-rose-600 hover:bg-rose-500 text-white text-sm font-semibold transition active:scale-95 shadow-sm">
                    End Meeting
                </button>
                @endif
                <button id="leaveMeetingBtn" class="h-10 px-4 rounded-lg border border-slate-600 bg-[#14233d] hover:bg-[#1a2d49] text-white text-sm font-semibold transition active:scale-95 shadow-sm">
                    Leave
                </button>
            </div>
        </div>

        <div class="flex flex-1 w-full overflow-hidden relative bg-[#02060d]">

            <div class="flex-1 flex flex-col justify-between overflow-hidden bg-[#02060d]">

                <div class="flex-1 p-4 md:p-6 overflow-hidden flex flex-col justify-center">
                    <div id="gridContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 items-center justify-center auto-rows-fr max-w-6xl mx-auto w-full overflow-y-auto">

                        <div class="relative w-full h-full min-h-[220px] bg-[#0a1422] rounded-xl overflow-hidden border border-slate-700/80 shadow-sm">
                            <video id="localVideo" autoplay playsinline muted class="w-full h-full object-cover"></video>
                            <span class="absolute bottom-3 left-3 bg-[#0b1220]/85 backdrop-blur text-xs px-3 py-1 rounded-lg border border-slate-700 text-slate-200 font-medium">You</span>
                        </div>

                        <div id="waitingMessage" class="hidden text-center text-slate-400 py-10 col-span-full">
                            <p class="text-xl mb-1 text-slate-200 font-semibold">Room Setup Complete</p>
                            <p class="text-sm text-slate-500">Waiting for other participants to connect...</p>
                        </div>
                    </div>
                </div>

                <div class="w-full h-16 bg-[#071120] border-t border-slate-800 px-4 md:px-6 flex items-center justify-center z-10 shrink-0">
                    <div class="flex flex-row items-center gap-2 whitespace-nowrap">
                        <button id="toggleAudioBtn" class="h-11 px-4 rounded-lg border border-slate-600 bg-[#14233d] hover:bg-[#1a2d49] text-white text-sm font-semibold transition active:scale-95 min-w-[90px] shadow-sm">
                            Mute
                        </button>
                        <button id="toggleVideoBtn" class="h-11 px-4 rounded-lg border border-slate-600 bg-[#14233d] hover:bg-[#1a2d49] text-white text-sm font-semibold transition active:scale-95 min-w-[112px] shadow-sm">
                            Camera Off
                        </button>
                        <button id="screenShareBtn" class="h-11 px-4 rounded-lg border border-slate-600 bg-[#14233d] hover:bg-[#1a2d49] text-white text-sm font-semibold transition active:scale-95 min-w-[122px] shadow-sm">
                            Share Screen
                        </button>
                        <button id="raiseHandBtn" class="h-11 px-4 rounded-lg bg-amber-600 hover:bg-amber-500 text-white text-sm font-semibold transition active:scale-95 min-w-[112px] shadow-sm">
                            Raise Hand ✋
                        </button>
                        <button id="recordBtn" class="h-11 px-4 rounded-lg bg-rose-600 hover:bg-rose-500 text-white text-sm font-semibold transition active:scale-95 min-w-[128px] shadow-sm">
                            🔴 Rec
                        </button>
                        <button id="toggleChatBtn" class="h-11 px-4 rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold transition active:scale-95 min-w-[92px] shadow-sm">
                            Chat
                        </button>
                    </div>
                </div>

            </div>

            <div id="recordingStatus" class="hidden fixed top-20 right-4 bg-rose-600 text-white px-4 py-2 rounded-lg shadow-lg z-50">
                <span class="flex items-center gap-2">
                    <span class="w-2 h-2 bg-white rounded-full animate-pulse"></span>
                    🔴 Recording...
                    <span id="recordingTimer" class="ml-2 font-mono">00:00</span>
                </span>
            </div>

            <div id="chatPanel" class="hidden w-80 h-full bg-[#08111e] border-l border-slate-800 flex flex-col shadow-xl transition-all duration-300 shrink-0">
                <div id="participantsSection" class="flex-1 flex flex-col overflow-hidden">
                    <div class="p-4 border-b border-slate-800 bg-[#071120]">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Participants</h2>
                            <span id="participantsBadge" class="text-[11px] text-slate-400">0</span>
                        </div>
                        <div id="participantsList" class="mt-3 space-y-2 overflow-y-auto"></div>
                    </div>
                </div>

                <div id="chatSection" class="hidden flex-1 flex flex-col overflow-hidden">
                    <div class="p-4 border-b border-slate-800 bg-[#071120]">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Meeting Chat</h2>
                    </div>

                    <div id="chatMessages" class="flex-1 p-4 overflow-y-auto space-y-3 scrollbar-thin bg-[#08111e]"></div>

                    <div class="w-full h-16 px-4 border-t border-slate-800 bg-[#071120] flex gap-2 items-center shrink-0">
                        <input type="text"
                            id="chatInput"
                            placeholder="Type a message..."
                            class="w-full bg-[#111c31] px-3 py-2 rounded-lg border border-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm text-slate-100 placeholder-slate-500 transition">
                        <button id="sendChatBtn" class="bg-blue-600 hover:bg-blue-500 text-white px-3.5 py-2 rounded-lg transition text-sm font-medium active:scale-95 shrink-0">
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

    <script src="https://unpkg.com/simple-peer@9.11.1/simplepeer.min.js"></script>
    <script>
        // ============================================================
        // READ VALUES
        // ============================================================
        const meetingId = parseInt(document.getElementById('meetingId').value) || 0;
        const authUserId = parseInt(document.getElementById('authUserId').value) || 0;
        const csrfToken = document.getElementById('csrfToken').value || '';
        const isHost = document.getElementById('isHost').value === '1';

        console.log('📌 Meeting ID:', meetingId);
        console.log('📌 User ID:', authUserId);
        console.log('📌 Is Host:', isHost);

        // ============================================================
        // GLOBAL VARIABLES
        // ============================================================
        let peers = {};
        let localStream = null;
        let videoElements = {};
        let participantNames = {};
        let participantListState = [];
        let sidebarView = 'participants';
        let isEnded = false;
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

        // Hand raise state
        window.isHandRaised = false;
        window.handRaiseStates = {};
        window.lastRealtimeHandRaiseTime = {};

        // Chat state
        let lastChatMessageId = 0;
        const receivedChatIds = new Set();
        let syncInterval = null;
        let echoListenersInitialized = false;
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
                    video: true
                })
                .then(function(stream) {
                    localStream = stream;
                    addVideo('local', stream, true);
                    console.log('✅ Camera started successfully');
                })
                .catch(function(err) {
                    console.error('❌ Camera error:', err);
                    alert('Please allow camera access and refresh.');
                });
        }

        // ============================================================
        // GENERATE PEER ID
        // ============================================================
        function generatePeerId() {
            myPeerId = 'user-' + authUserId + '-' + Date.now();
            console.log('🔑 MY PEER ID:', myPeerId);

            return fetch('/save-peer-id', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        peer_id: myPeerId
                    })
                })
                .then(function() {
                    console.log('✅ Peer ID saved to server');

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
                    console.error('❌ Error saving peer ID:', err);
                });
        }

        // ============================================================
        // PEER CONNECTION
        // ============================================================
        function createPeerConnection(peerId, isInitiator) {
            if (!localStream) {
                setTimeout(function() {
                    createPeerConnection(peerId, isInitiator);
                }, 500);
                return;
            }
            if (peers[peerId]) {
                console.log('Already connected to:', peerId);
                return;
            }
            if (isConnecting[peerId]) {
                console.log('Already connecting to:', peerId);
                return;
            }

            console.log('🔗 Creating peer connection to:', peerId, '| Initiator:', isInitiator);
            isConnecting[peerId] = true;

            const peer = new SimplePeer({
                initiator: isInitiator,
                stream: localStream,
                trickle: false,
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
                console.log('📡 Sending signal to:', peerId);
                sendSignal(peerId, data);
            });

            peer.on('stream', function(stream) {
                console.log('🎥 REMOTE STREAM RECEIVED from:', peerId);
                delete failedPeers[peerId];

                if (videoElements[peerId]) {
                    const videoEl = videoElements[peerId].querySelector('video');
                    if (videoEl) {
                        videoEl.srcObject = stream;
                        videoEl.play().catch(function() {});
                        console.log('✅ Updated existing video for:', peerId);
                    }
                } else {
                    addVideo(peerId, stream, false);
                }

                isConnecting[peerId] = false;
            });

            peer.on('connect', function() {
                console.log('🔗 Connected to:', peerId);
                isConnecting[peerId] = false;
            });

            peer.on('data', function(data) {
                try {
                    const payload = JSON.parse(data.toString());
                    console.log('📦 Peer data received:', payload);
                    handlePeerData(payload, peerId);
                } catch (e) {
                    console.warn('Failed to parse peer data:', e);
                }
            });

            peer.on('close', function() {
                console.log('❌ Peer closed connection:', peerId);
                removeVideo(peerId);
                delete peers[peerId];
                delete isConnecting[peerId];
            });

            peer.on('error', function(err) {
                console.error('❌ Peer connection error:', err);
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
        function sendSignal(peerId, signalData) {
            const cleanSignal = sanitizeSignal(signalData);
            fetch('/meeting/signal', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    meeting_id: meetingId,
                    peer_id: myPeerId,
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

            if (!peers[fromPeerId]) {
                pendingSignals[fromPeerId] = pendingSignals[fromPeerId] || [];
                pendingSignals[fromPeerId].push(parsedSignal);
                createPeerConnection(fromPeerId, false);
                return;
            }

            try {
                peers[fromPeerId].signal(parsedSignal);
            } catch (e) {}
        }

        // ============================================================
        // PARTICIPANTS SYNC HANDLER
        // ============================================================
        function handleParticipantsSync(data) {
            if (!data) return;

            const count = data.length;
            document.getElementById('participantCount').textContent = ' ' + count + ' participant(s)';
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
                    participantNames[p.peer_id] = p.user_name;
                    participantNames[p.peer_id + '_userId'] = p.user_id;
                }
            });

            // Update mute/video status
            data.forEach(function(p) {
                const isMe = (p.user_id == authUserId);
                const boxId = isMe
                    ? (myPeerId ? 'video-' + myPeerId : 'video-local')
                    : 'video-' + p.peer_id;
                const videoBox = document.getElementById(boxId);

                if (videoBox) {
                    let badge = videoBox.querySelector('.mute-indicator');
                    if (p.is_audio_muted) {
                        if (!badge) {
                            badge = document.createElement('span');
                            badge.className = 'mute-indicator absolute top-3 right-3 bg-red-600/80 text-white px-2 py-0.5 rounded text-xs font-bold z-10';
                            badge.textContent = ' MUTED';
                            videoBox.appendChild(badge);
                        }
                    } else if (badge) {
                        badge.remove();
                    }

                    const videoEl = videoBox.querySelector('video');
                    if (videoEl) {
                        videoEl.style.opacity = p.is_video_off ? '0.15' : '1';
                    }

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
                                raiseHandBtn.innerHTML = window.isHandRaised ? 'Lower Hand ✋' : 'Raise Hand ✋';
                                raiseHandBtn.classList.toggle('bg-amber-700', window.isHandRaised);
                                raiseHandBtn.classList.toggle('bg-amber-600', !window.isHandRaised);
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
                        console.log('🔗 Outgoing connection to:', p.user_name);
                        setTimeout(function() {
                            createPeerConnection(p.peer_id, true);
                        }, 1000);
                    } else {
                        console.log('⏳ Expecting incoming connection from:', p.user_name);
                    }
                }
            });

            // Remove peers that are no longer in the meeting
            Object.keys(peers).forEach(function(peerId) {
                let exists = data.some(p => p.peer_id === peerId);
                if (!exists) {
                    console.log('🗑️ Removing peer:', peerId);
                    removeVideo(peerId);
                }
            });
        }

        // ============================================================
        // UNIFIED SYNC POLLING LOOPS
        // ============================================================
        function startSyncPolling() {
            if (syncInterval) clearInterval(syncInterval);
            runSync();
            syncInterval = setInterval(function() {
                if (!isEnded) runSync();
            }, 1000);
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
            })
            .catch(err => {
                isSyncing = false;
                console.error('Sync error:', err);
            });
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
                    handBadge.textContent = '✋ Hand raised';
                    titleRow.appendChild(handBadge);
                }

                if (participant.is_host) {
                    const hostBadge = document.createElement('span');
                    hostBadge.className = 'inline-flex items-center rounded-full bg-amber-500/15 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-amber-300';
                    hostBadge.textContent = '✪ Host';
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
                    muteBtn.innerHTML = participant.is_audio_muted ? '🔇' : '🔊';
                    muteBtn.title = participant.is_audio_muted ? 'Unmute participant' : 'Mute participant';
                    muteBtn.addEventListener('click', function() {
                        toggleRemoteParticipantAudio(participant.user_id, participant.is_audio_muted);
                    });

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'w-7 h-7 flex items-center justify-center rounded bg-rose-700 hover:bg-rose-600 text-white';
                    removeBtn.innerHTML = '✕';
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
                    }

                    const waiting = document.getElementById('waitingMessage');
                    if (waiting && grid.children.length > 1) waiting.style.display = 'none';
                    return;
                }
            }

            const name = participantNames[peerId] || 'User';

            const div = document.createElement('div');
            div.id = 'video-' + peerId;
            if (participantNames[peerId + '_userId']) {
                div.dataset.userId = participantNames[peerId + '_userId'];
            }
            div.className = 'relative w-full h-full min-h-[220px] bg-slate-800 rounded-xl overflow-hidden border border-slate-700 shadow-lg aspect-video';

            const video = document.createElement('video');
            video.autoplay = true;
            video.playsInline = true;
            video.muted = false;
            video.srcObject = stream;
            video.className = 'w-full h-full object-cover';

            const label = document.createElement('div');
            label.className = 'absolute bottom-3 left-3 bg-slate-950/70 backdrop-blur text-xs px-3 py-1 rounded-md border border-slate-700 text-slate-300 font-medium';
            label.textContent = name;

            div.appendChild(video);
            div.appendChild(label);
            grid.appendChild(div);

            video.play().catch(function(err) {
                console.warn('Video play failed:', err);
            });

            videoElements[peerId] = div;

            // Apply visual hand-raised badge if already raised
            const key = peerId;
            const userId = participantNames[peerId + '_userId'];
            const handRaised = !!(window.handRaiseStates[key] || (userId && window.handRaiseStates[userId]));
            if (handRaised) {
                globalRenderHandRaise(div, true);
            }

            const waiting = document.getElementById('waitingMessage');
            if (waiting && grid.children.length > 1) waiting.style.display = 'none';

            console.log('✅ Added video for:', name);
        }

        function removeVideo(peerId) {
            if (videoElements[peerId]) {
                const localVideo = videoElements[peerId].querySelector('#localVideo');
                if (localVideo) {
                    localVideo.srcObject = null;
                } else {
                    videoElements[peerId].remove();
                }
                delete videoElements[peerId];
            }

            if (peers[peerId]) {
                try {
                    peers[peerId].destroy();
                } catch (e) {}
                delete peers[peerId];
            }
            delete isConnecting[peerId];
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

                    messages.forEach(function(msg) {
                        if (!msg || receivedChatIds.has(msg.id)) return;
                        receivedChatIds.add(msg.id);
                        lastChatMessageId = Math.max(lastChatMessageId, msg.id);

                        if (msg.user_id == authUserId) return;

                        appendMessage(msg.sender_name || 'User', msg.message, false);
                    });
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
            console.log('🔍 ECHO DEBUG:');
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
                console.warn('⚠️ Echo websocket listener not active');
                return;
            }

            if (!window.Echo.connector || window.Echo.connector.pusher?.connection?.state !== 'connected') {
                console.log('⏳ Waiting for Pusher connection...');
                setTimeout(initEchoListeners, 500);
                return;
            }

            echoListenersInitialized = true;
            console.log('🔌 Initializing Echo listeners for meeting:', meetingId);

            const meetingChannel = window.Echo.channel('meeting.' + meetingId);

            meetingChannel
                // ✅ HAND RAISE - Server Event
                .listen('.user-raised-hand', function(payload) {
                    console.log('🔥🔥🔥 HAND RAISE RECEIVED (server event):', payload);
                    console.log('📌 Payload userId:', payload.userId);
                    console.log('📌 Auth userId:', authUserId);
                    console.log('📌 Is this my own hand raise?', payload.userId == authUserId);

                    if (payload.userId == authUserId) {
                        console.log('⏭️ Skipping own hand raise');
                        return;
                    }

                    console.log('✅ Proceeding to handle hand raise for other user');
                    handleIncomingHandRaise(payload);
                })
                // ✅ CHAT MESSAGE - Server Event
                .listen('.chat-message', function(payload) {
                    console.log('💬 Chat message received (server):', payload);
                    handleIncomingChatMessage(payload);
                })
                // ✅ CHAT MESSAGES - Client Whisper
                .listenForWhisper('chat-message', function(payload) {
                    console.log('💬 Chat message received (whisper):', payload);
                    handleIncomingChatMessage(payload);
                })
                // ✅ HAND RAISE - Client Whisper
                .listenForWhisper('hand-raised', function(payload) {
                    console.log('🔥 Hand raise received (whisper):', payload);
                    handleIncomingHandRaise(payload);
                })
                // ✅ PARTICIPANT JOINED - Now on public channel
                .listen('ParticipantJoined', function(data) {
                    console.log('👤 Participant joined:', data);

                    const p = data.participant;
                    if (!p || p.user_id == authUserId || isEnded) {
                        console.log('⏭️ Skipping join event');
                        return;
                    }

                    if (p.peer_id) {
                        participantNames[p.peer_id] = p.user_name || 'User';
                        console.log('👤 Participant stored:', p.user_name, 'Peer:', p.peer_id);
                    }

                    runSync();
                })
                // ✅ MEETING ENDED - Now on public channel
                .listen('MeetingEnded', function(data) {
                    console.log('📢 Meeting ended event received:', data);
                    endMeetingForAll(data.message || 'Meeting ended by host');
                })
                // ✅ MEETING ENDED (Whisper fallback) - Now on public channel
                .listenForWhisper('meeting-ended', function(data) {
                    console.log('📢 Meeting ended whisper received:', data);
                    endMeetingForAll(data.message || 'Meeting ended by host');
                })
                // ✅ PARTICIPANT KICKED - Now on public channel
                .listenForWhisper('participant-kicked', function(data) {
                    console.log('🚫 Participant kicked:', data);

                    if (data && data.user_id == authUserId) {
                        endMeetingForAll(data.message || 'You have been removed from the meeting');
                    }
                })
                // ✅ USER LEFT - Now on public channel
                .listenForWhisper('user-left', function(data) {
                    console.log('👋 User left:', data);

                    if (!data || data.user_id == authUserId) {
                        console.log('⏭️ Skipping own leave event');
                        return;
                    }

                    runSync();
                });

            console.log('✅ Echo listeners initialized (public meeting channel)');
        }

        function getMeetingChannel() {
            if (!window.Echo) return null;
            return window.Echo.channel('meeting.' + meetingId);
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
            bubble.className = (isOwn
                ? 'bg-blue-600 text-white'
                : 'bg-[#111c31] text-slate-100 border border-slate-700') + ' rounded-xl px-3 py-2 max-w-[85%] break-words text-sm shadow-sm';

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
            alert(message || 'Meeting has ended');
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

        function copyLink() {
            const url = window.location.href;
            const btn = document.getElementById('copyLinkBtn');
            navigator.clipboard.writeText(url).then(() => {
                btn.textContent = 'Copied!';
                setTimeout(() => {
                    btn.textContent = 'Copy Link';
                }, 2000);
            }).catch(() => prompt('Copy this link:', url));
        }

        function syncStatusToServer() {
            if (!localStream) return;
            const audioTrack = localStream.getAudioTracks()[0];
            const videoTrack = localStream.getVideoTracks()[0];
            const isMuted = audioTrack ? !audioTrack.enabled : true;
            const isVideoOff = videoTrack ? !videoTrack.enabled : true;

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
                btn.textContent = track.enabled ? 'Mute' : 'Unmute';
                btn.classList.toggle('bg-red-600', !track.enabled);
                syncStatusToServer();
            }
        }

        function toggleVideo() {
            if (!localStream) return;
            const track = localStream.getVideoTracks()[0];
            if (track) {
                track.enabled = !track.enabled;
                const btn = document.getElementById('toggleVideoBtn');
                btn.textContent = track.enabled ? 'Camera Off' : 'Camera On';
                btn.classList.toggle('bg-red-600', !track.enabled);
                syncStatusToServer();
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

                    const screenTrack = screenStream.getVideoTracks()[0];
                    if (localStream) {
                        const originalCameraTrack = localStream.getVideoTracks()[0];
                        if (originalCameraTrack) {
                            localStream.removeTrack(originalCameraTrack);
                        }
                        localStream.addTrack(screenTrack);
                    }

                    Object.keys(peers).forEach(peerId => {
                        const nativePC = findNativePeerConnection(peers[peerId]);
                        if (nativePC) {
                            const senders = nativePC.getSenders();
                            const videoSender = senders.find(s => s.track && s.track.kind === 'video');
                            if (videoSender) {
                                videoSender.replaceTrack(screenTrack);
                            }
                        }
                    });

                    const localVideoEl = document.getElementById('localVideo');
                    if (localVideoEl) {
                        localVideoEl.srcObject = screenStream;
                    }

                    screenTrack.onended = () => stopScreenShare();

                    screenBtn.innerHTML = 'Stop Sharing';
                    screenBtn.className = 'bg-rose-600 hover:bg-rose-700 text-white px-6 py-2 rounded-lg transition';
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
            }

            if (localStream) {
                // Restore camera track - simplified
                navigator.mediaDevices.getUserMedia({
                        video: true
                    })
                    .then(stream => {
                        const newCameraTrack = stream.getVideoTracks()[0];
                        if (newCameraTrack) {
                            const oldScreenTrack = localStream.getVideoTracks()[0];
                            if (oldScreenTrack) {
                                localStream.removeTrack(oldScreenTrack);
                            }
                            localStream.addTrack(newCameraTrack);
                        }
                    })
                    .catch(err => console.error('Failed to restore camera:', err));
            }

            if (screenBtn) {
                screenBtn.innerHTML = 'Share Screen';
                screenBtn.className = 'bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg transition';
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
                    console.log('📤 Chat sent via server event:', data);
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
                console.warn('⚠️ No container provided for hand raise');
                return;
            }

            // If video element passed, get its parent
            if (containerElement.tagName && containerElement.tagName.toLowerCase() === 'video') {
                containerElement = containerElement.parentElement;
            }

            if (!containerElement) {
                console.warn('⚠️ Could not find parent container for video');
                return;
            }

            // Make sure container has position relative
            containerElement.style.position = 'relative';

            // Remove existing badge
            let existingBadge = containerElement.querySelector('.hand-raise-badge');
            if (existingBadge) {
                existingBadge.remove();
                console.log('🗑️ Removed existing badge');
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
                badge.textContent = '✋ RAISED';
                badge.style.cssText = `
                    position: absolute;
                    top: 12px;
                    right: 12px;
                    background: #f59e0b;
                    color: #ffffff;
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
                console.log('✅ Hand raise badge rendered');
            } else {
                console.log('✅ Hand raise badge removed');
            }
        }

        // ============================================================
        // HAND RAISE - IMPROVED VERSION
        // ============================================================
        function handleIncomingHandRaise(payload) {
            console.log("📥 HAND RAISE RECEIVED:", payload);
            if (!payload) return;

            const userId = payload.userId !== undefined ? payload.userId : payload.user_id;
            const peerId = payload.peerId !== undefined ? payload.peerId : payload.peer_id;
            const userName = payload.userName !== undefined ? payload.userName : payload.user_name;
            const raised = payload.raised !== undefined ? payload.raised : payload.hand_raised;

            if (userId == authUserId) {
                console.log('⏭️ Skipping own hand raise');
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
                    console.log('✅ Found container by peerId:', peerId);
                }
            }

            // Method 2: Search by userId in data attribute
            if (!remoteBox && userId) {
                const allVideos = document.querySelectorAll('[id^="video-"]');
                for (const box of allVideos) {
                    if (box.dataset && box.dataset.userId == userId) {
                        remoteBox = box;
                        console.log('✅ Found container by userId:', userId);
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
                        console.log('✅ Found container by userName:', userName);
                        break;
                    }
                }
            }

            if (remoteBox) {
                console.log('✅ Rendering hand raise badge');
                globalRenderHandRaise(remoteBox, !!raised);
            } else {
                console.warn('⚠️ Could not find container for peer:', peerId || userId);

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
                        console.log('✅ Found container after delay');
                        globalRenderHandRaise(retryBox, !!raised);
                    }
                }, 2000);
            }
        }

        // ============================================================
        // RECORDING
        // ============================================================
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
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `meeting-recording-${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.webm`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                };

                mediaRecorder.start(1000);
                isRecording = true;

                const recordBtn = document.getElementById('recordBtn');
                recordBtn.innerHTML = '⏹️ Stop Recording';
                recordBtn.className = 'bg-red-800 hover:bg-red-900 text-white px-4 py-1.5 rounded-xl text-xs font-semibold transition active:scale-95 shadow-md min-w-[105px] animate-pulse';

                document.getElementById('recordingStatus').classList.remove('hidden');
                recordingSeconds = 0;
                if (recordingTimer) clearInterval(recordingTimer);
                recordingTimer = setInterval(() => {
                    recordingSeconds++;
                    const mins = String(Math.floor(recordingSeconds / 60)).padStart(2, '0');
                    const secs = String(recordingSeconds % 60).padStart(2, '0');
                    document.getElementById('recordingTimer').textContent = `${mins}:${secs}`;
                }, 1000);

                console.log('🎥 Recording started from browser tab');

            } catch (error) {
                console.error('Failed to start recording:', error);
                alert('Failed to start recording: ' + error.message);
            }
        }

        function stopRecording() {
            if (mediaRecorder && isRecording) {
                mediaRecorder.stop();
                isRecording = false;

                const recordBtn = document.getElementById('recordBtn');
                recordBtn.innerHTML = '🔴 Record Meeting';
                recordBtn.className = 'bg-red-600 hover:bg-red-700 text-white px-4 py-1.5 rounded-xl text-xs font-semibold transition active:scale-95 shadow-md min-w-[115px]';

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

                console.log('⏹️ Recording stopped');
            }
        }

        function toggleRecording() {
            if (isRecording) {
                stopRecording();
            } else {
                startRecording();
            }
        }

        // ============================================================
        // TIMER
        // ============================================================
        function startMeetingTimer() {
            const timerElement = document.getElementById('meetingTimer');
            if (!timerElement) return;
            let totalSeconds = 0;

            setInterval(() => {
                totalSeconds++;
                const hrs = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
                const mins = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
                const secs = String(totalSeconds % 60).padStart(2, '0');
                timerElement.textContent = `${hrs}:${mins}:${secs}`;
            }, 1000);
        }

        // ============================================================
        // DOMContentLoaded - Initialize Everything
        // ============================================================
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 UI Framework Ready');

            // Button listeners
            document.getElementById('copyLinkBtn').addEventListener('click', copyLink);
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

                console.log('🖐️ Hand raise toggled:', newRaisedState ? 'RAISED' : 'LOWERED');

                // Update UI immediately for self
                raiseHandBtn.innerHTML = window.isHandRaised ? 'Lower Hand ✋' : 'Raise Hand ✋';
                raiseHandBtn.classList.toggle('bg-amber-700', window.isHandRaised);
                raiseHandBtn.classList.toggle('bg-amber-600', !window.isHandRaised);

                let localBox = document.getElementById('localVideo')?.parentElement;
                if (localBox) {
                    globalRenderHandRaise(localBox, window.isHandRaised);
                }

                const activePeerId = myPeerId;
                if (!activePeerId) {
                    console.warn('⚠️ Peer ID not ready yet');
                    window.isHandRaised = !newRaisedState;
                    raiseHandBtn.innerHTML = window.isHandRaised ? 'Lower Hand ✋' : 'Raise Hand ✋';
                    raiseHandBtn.classList.toggle('bg-amber-700', window.isHandRaised);
                    raiseHandBtn.classList.toggle('bg-amber-600', !window.isHandRaised);
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
                        console.log('📤 Hand raise sent via server event:', data);
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

            // Start peer ID generation and camera
            generatePeerId();
            startCamera();
        });

        // ============================================================
        // WINDOW EVENTS
        // ============================================================
        window.addEventListener('beforeunload', function() {
            if (syncInterval) clearInterval(syncInterval);
            if (isRecording) stopRecording();
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
    </style>
</x-app-layout>