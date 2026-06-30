<x-app-layout>
    <div class="h-screen bg-slate-900 flex flex-col">
        <!-- Top Bar -->
        <div class="bg-slate-800 px-6 py-4 flex justify-between items-center border-b border-slate-700">
            <div class="flex items-center gap-4">
                <h1 class="text-white font-bold text-lg">
                    🎥 <span class="text-blue-400">{{ $meeting->meeting_name ?? 'Meeting' }}</span>
                </h1>
                <span class="text-slate-400 text-sm">
                    Code: <span class="text-white font-mono bg-slate-700 px-3 py-1 rounded-lg">{{ $meeting->room_id ?? 'N/A' }}</span>
                </span>
                <span class="text-slate-400 text-sm" id="participantCount">
                    👥 1 participant(s)
                </span>
            </div>
            <div class="flex gap-3">
                <button id="copyLinkBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition text-sm">
                    📋 Copy Link
                </button>
                @if(Auth::id() == $meeting->created_by)
                <button id="endMeetingBtn" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition text-sm font-semibold">
                    ⛔ End Meeting
                </button>
                @endif
                <button id="leaveMeetingBtn" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition text-sm">
                    🚪 Leave
                </button>
            </div>
        </div>

        <!-- Video Grid -->
        <div class="flex-1 p-4 overflow-y-auto">
            <div id="gridContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <div id="waitingMessage" class="text-center text-slate-400 py-20 col-span-full">
                    <p class="text-2xl mb-2">🎥</p>
                    <p>Waiting for participants to join...</p>
                </div>
            </div>
        </div>

        <!-- Controls -->
        <div class="bg-slate-800 px-6 py-4 flex justify-center gap-4 border-t border-slate-700">
            <button id="toggleAudioBtn" class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-2 rounded-lg transition">
                🎤 Mute
            </button>
            <button id="toggleVideoBtn" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg transition">
                📷 Camera Off
            </button>
        </div>
    </div>

    <!-- Hidden inputs -->
    <input type="hidden" id="meetingId" value="{{ $meeting->id ?? 0 }}">
    <input type="hidden" id="authUserId" value="{{ Auth::id() }}">
    <input type="hidden" id="csrfToken" value="{{ csrf_token() }}">
    <input type="hidden" id="userName" value="{{ Auth::user()->name ?? 'User' }}">
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

        console.log('📋 Meeting ID:', meetingId);
        console.log('👤 User ID:', authUserId);
        console.log('👑 Is Host:', isHost);

        // ============================================================
        // GLOBAL VARIABLES
        // ============================================================
        let peers = {};
        let localStream = null;
        let videoElements = {};
        let participantNames = {};
        let isEnded = false;
        let refreshInterval = null;
        let myPeerId = null;
        let isConnecting = {};
        let pendingSignals = {};
        let failedPeers = {};

        function sanitizeSDP(sdp) {
            if (!sdp || typeof sdp !== 'string') {
                return sdp;
            }

            const cleaned = sdp
                .replace(/\r\n/g, '\n')
                .split('\n')
                .filter(function(line) {
                    return line.length > 0;
                })
                .join('\r\n');

            return cleaned.endsWith('\r\n') ? cleaned : cleaned + '\r\n';
        }

        function sanitizeSignal(signalData) {
            if (!signalData || typeof signalData !== 'object') {
                return signalData;
            }

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
                alert('Camera requires HTTPS on this network. Open https://192.168.1.41:8443 instead of http.');
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
            console.log('🆔 MY PEER ID:', myPeerId);

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
                    getParticipants();
                    initEchoListeners();
                    startAutoRefresh();
                    startSignalPolling();
                })
                .catch(function(err) {
                    console.error('Error saving peer ID:', err);
                });
        }

        // ============================================================
        // CREATE PEER CONNECTION
        // ============================================================
        function createPeerConnection(peerId, isInitiator) {
            if (!localStream) {
                setTimeout(function() {
                    createPeerConnection(peerId, isInitiator);
                }, 500);
                return;
            }

            if (peers[peerId]) {
                console.log('⏭️ Already connected to:', peerId);
                return;
            }
            if (isConnecting[peerId]) {
                console.log('⏭️ Already connecting to:', peerId);
                return;
            }

            console.log('🔗 Creating peer connection to:', peerId, ' | Initiator Role:', isInitiator);
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
                console.log('📡 Sending signal to target:', peerId);
                sendSignal(peerId, data);
            });

            peer.on('stream', function(stream) {
                console.log('✅ REMOTE STREAM RECEIVED from:', peerId);
                delete failedPeers[peerId];
                addVideo(peerId, stream, false);
                isConnecting[peerId] = false;
            });

            peer.on('connect', function() {
                isConnecting[peerId] = false;
            });

            peer.on('close', function() {
                console.log('🔌 Peer closed connection:', peerId);
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
                        } catch (e) {
                            console.error('❌ Deferred signal failed for:', peerId, e);
                        }
                    });

                    delete pendingSignals[peerId];
                }, 0);
            }
        }

        // ============================================================
        // SEND SIGNAL
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
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        console.error('❌ Failed to distribute signal payload:', data);
                    }
                })
                .catch(err => console.error('Error sending signal:', err));
        }

        // ============================================================
        // PROCESS INCOMING SIGNAL
        // ============================================================
        function processSignal(fromPeerId, signalData) {
            console.log('📡 Processing signal arriving from:', fromPeerId);

            // Safety handling: Ensure signal data is parsed if it arrived as a raw string
            let parsedSignal = signalData;
            if (typeof signalData === 'string') {
                try {
                    parsedSignal = JSON.parse(signalData);
                } catch (e) {}
            }

            parsedSignal = sanitizeSignal(parsedSignal);

            if (!peers[fromPeerId]) {
                console.log('📡 Creating non-initiating receiving peer container for:', fromPeerId);
                pendingSignals[fromPeerId] = pendingSignals[fromPeerId] || [];
                pendingSignals[fromPeerId].push(parsedSignal);
                createPeerConnection(fromPeerId, false);
                return;
            }

            try {
                peers[fromPeerId].signal(parsedSignal);
            } catch (e) {
                console.error('❌ Signal failed for:', fromPeerId, e);
            }
        }

        // ============================================================
        // POLL FOR SIGNALS
        // ============================================================
        function pollSignals() {
            if (isEnded || !myPeerId) return;

            fetch('/meeting/signals/' + meetingId + '/' + myPeerId)
                .then(res => res.json())
                .then(function(data) {
                    if (data.error) return;
                    data.forEach(function(signal) {
                        processSignal(signal.from_peer_id, signal.signal_data);
                    });
                })
                .catch(err => console.error('Error polling signals:', err));
        }

        // ============================================================
        // GET PARTICIPANTS
        // ============================================================
        function getParticipants() {
            if (isEnded) return;

            fetch('/meeting/participants/' + meetingId)
                .then(res => res.json())
                .then(function(data) {
                    document.getElementById('participantCount').textContent = '👥 ' + data.length + ' participant(s)';

                    if (data.length === 0 && !isHost) {
                        endMeetingForAll('Meeting ended by host');
                        return;
                    }

                    data.forEach(function(p) {
                        if (p.user_id != authUserId) {
                            participantNames[p.peer_id] = p.user_name;
                        }
                    });

                    // Connect to new participants systematically
                    data.forEach(function(p) {
                        if (p.user_id != authUserId && p.peer_id && !peers[p.peer_id] && !isConnecting[p.peer_id]) {
                            const failedAt = failedPeers[p.peer_id];
                            if (failedAt && (Date.now() - failedAt) < 15000) {
                                return;
                            }

                            const shouldIInitiate = myPeerId > p.peer_id;

                            if (shouldIInitiate) {
                                console.log('📞 Outgoing connection call setup to:', p.user_name);
                                setTimeout(function() {
                                    createPeerConnection(p.peer_id, true);
                                }, 1000);
                            } else {
                                console.log('⏳ Expecting incoming connection offer from:', p.user_name);
                            }
                        }
                    });

                    // Cleanup missing peers
                    Object.keys(peers).forEach(function(peerId) {
                        let exists = data.some(p => p.peer_id === peerId);
                        if (!exists) {
                            removeVideo(peerId);
                        }
                    });
                })
                .catch(err => console.error('Participant loop issue:', err));
        }

        // ============================================================
        // VIDEO GRID RENDERER
        // ============================================================
        function addVideo(peerId, stream, isLocal) {
            if (videoElements[peerId]) return;
            if (isEnded) return;

            const grid = document.getElementById('gridContainer');
            const name = isLocal ? 'You' : (participantNames[peerId] || 'User');

            const div = document.createElement('div');
            div.id = 'video-' + peerId;
            div.className = 'relative bg-black rounded-xl overflow-hidden aspect-video';

            const video = document.createElement('video');
            video.autoplay = true;
            video.playsInline = true;
            video.muted = isLocal; // Never loop local microphone output back to yourself
            video.srcObject = stream;

            const label = document.createElement('div');
            label.className = 'absolute bottom-3 left-3 text-white bg-black/60 px-3 py-1 rounded-lg text-sm';
            label.textContent = name;

            div.appendChild(video);
            div.appendChild(label);
            grid.appendChild(div);

            video.play().catch(function() {});

            videoElements[peerId] = div;

            const waiting = document.getElementById('waitingMessage');
            if (waiting && grid.children.length > 1) waiting.style.display = 'none';
        }

        function removeVideo(peerId) {
            if (videoElements[peerId]) {
                videoElements[peerId].remove();
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
        // LOOPS & LIFECYCLE
        // ============================================================
        function startAutoRefresh() {
            if (refreshInterval) clearInterval(refreshInterval);
            refreshInterval = setInterval(function() {
                if (!isEnded) getParticipants();
            }, 3000);
        }

        function startSignalPolling() {
            setInterval(function() {
                pollSignals();
            }, 2000);
        }

        function initEchoListeners() {
            if (typeof window.Echo === 'undefined') {
                console.warn('Echo websocket listener not active');
                return;
            }

            window.Echo.private('meeting.' + meetingId)
                .listen('ParticipantJoined', function(data) {
                    const p = data.participant;
                    if (p.user_id == authUserId) return;
                    if (isEnded) return;

                    participantNames[p.peer_id] = p.user_name;
                    console.log('👤 Push Notification: Participant entered:', p.user_name);
                })
                .listen('MeetingEnded', function() {
                    endMeetingForAll('Meeting ended by host');
                })
                .listenForWhisper('meeting-ended', function() {
                    endMeetingForAll('Meeting ended by host');
                });
        }

        // ============================================================
        // TEARDOWN METHODS
        // ============================================================
        function endMeeting() {
            if (!isHost) return;
            if (!confirm('End the meeting for all participants?')) return;

            if (window.Echo) {
                window.Echo.private('meeting.' + meetingId).whisper('meeting-ended', {});
            }

            fetch('/meeting/end/' + meetingId, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .finally(() => {
                    closeAll();
                    window.location.href = '/dashboard';
                });
        }

        function endMeetingForAll(message) {
            if (isEnded) return;
            alert(message);
            closeAll();
            window.location.href = '/dashboard';
        }

        function closeAll() {
            isEnded = true;
            if (refreshInterval) clearInterval(refreshInterval);

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
            closeAll();
            fetch('/meeting/leave', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        meeting_id: meetingId
                    })
                })
                .finally(() => {
                    window.location.href = '/dashboard';
                });
        }

        // ============================================================
        // BUTTON INTERACTION TRIGGERS
        // ============================================================
        function toggleAudio() {
            if (!localStream) return;
            const track = localStream.getAudioTracks()[0];
            if (track) {
                track.enabled = !track.enabled;
                const btn = document.getElementById('toggleAudioBtn');
                btn.textContent = track.enabled ? '🎤 Mute' : '🔊 Unmute';
                btn.classList.toggle('bg-red-600', !track.enabled);
                btn.classList.toggle('bg-yellow-600', track.enabled);
            }
        }

        function toggleVideo() {
            if (!localStream) return;
            const track = localStream.getVideoTracks()[0];
            if (track) {
                track.enabled = !track.enabled;
                const btn = document.getElementById('toggleVideoBtn');
                btn.textContent = track.enabled ? '📷 Camera Off' : '📷 Camera On';
                btn.classList.toggle('bg-red-600', !track.enabled);
                btn.classList.toggle('bg-indigo-600', track.enabled);
            }
        }

        function copyLink() {
            const url = window.location.href;
            const btn = document.getElementById('copyLinkBtn');
            navigator.clipboard.writeText(url).then(() => {
                btn.textContent = '✅ Copied!';
                setTimeout(() => {
                    btn.textContent = '📋 Copy Link';
                }, 2000);
            }).catch(() => prompt('Copy this link:', url));
        }

        // ============================================================
        // APPLICATION ENTRY POINT
        // ============================================================
        document.addEventListener('DOMContentLoaded', function() {
            console.log('📄 UI Framework Ready');

            document.getElementById('copyLinkBtn').addEventListener('click', copyLink);
            document.getElementById('leaveMeetingBtn').addEventListener('click', leaveMeeting);
            document.getElementById('toggleAudioBtn').addEventListener('click', toggleAudio);
            document.getElementById('toggleVideoBtn').addEventListener('click', toggleVideo);

            const endBtn = document.getElementById('endMeetingBtn');
            if (endBtn) endBtn.addEventListener('click', endMeeting);

            // Save peer ID immediately so signaling works, start camera in parallel
            generatePeerId();
            startCamera();
        });

        window.addEventListener('beforeunload', function() {
            if (refreshInterval) clearInterval(refreshInterval);
        });
    </script>
</x-app-layout>