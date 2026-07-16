<x-app-layout>
    <!-- Hidden inputs -->
    <input type="hidden" id="authUserId" value="{{ Auth::id() }}">
    <input type="hidden" id="csrfToken" value="{{ csrf_token() }}">
    <input type="hidden" id="userName" value="{{ Auth::user()->name }}">

    <div class="h-screen bg-slate-950 flex text-slate-100">
        <!-- ============================================================
        LEFT SIDEBAR
        ============================================================ -->
        <div class="w-80 bg-slate-900 text-white flex flex-col flex-shrink-0 border-r border-slate-800">

            <!-- BRAND -->
            <div class="p-6 border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <img src="/images/logo.png" class="h-14 object-contain" alt="Logo">
                    <div>
                        <h1 class="text-2xl font-bold text-indigo-400">PeerConnect</h1>
                        <p class="text-slate-400 text-[11px] font-medium mt-0.5 whitespace-nowrap">Video Meetings & Chat</p>
                    </div>
                </div>
            </div>

            <!-- FRIENDS LIST PLACEHOLDER -->
            <div class="flex-1 overflow-y-auto">
            </div>

            <!-- LOGOUT BUTTON -->
            <div class="p-4 border-t border-slate-800">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 px-4 py-2.5 rounded-xl transition flex items-center gap-3 font-semibold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </div>

        <!-- ============================================================
        RIGHT CHAT AREA
        ============================================================ -->
        <div class="flex-1 flex flex-col bg-slate-950">
            <!-- HEADER -->
            <div class="h-20 border-b border-slate-800 flex items-center justify-between px-6 bg-slate-900 shadow-md">
                <div id="headerLeft">
                    <div id="appName" class="hidden"></div>
                    <div id="friendInfo" class="hidden flex items-center gap-2">
                        <span id="selectedUser" class="font-bold text-xl text-white">Friend Name</span>
                        <span id="onlineStatus" class="text-sm text-emerald-400">● Online</span>
                    </div>
                </div>

                <div id="headerRight" class="flex gap-2 items-center">
                    <div id="dashboardButtons" class="flex gap-2">
                        <a href="{{ route('meeting.create') }}"
                            class="h-10 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 border border-indigo-700/50 text-white text-sm font-semibold flex items-center gap-2 transition active:scale-95 shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            Host
                        </a>
                        <button id="joinMeetingBtn"
                            class="h-10 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 border border-indigo-700/50 text-white text-sm font-semibold flex items-center gap-2 transition active:scale-95 shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Join
                        </button>
                        <a href="{{ route('recordings.index') }}"
                            class="h-10 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 border border-indigo-700/50 text-white text-sm font-semibold flex items-center gap-2 transition active:scale-95 shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            Recordings
                        </a>
                        <a id="settingsBtn" href="{{ route('profile.edit') }}"
                            class="h-10 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 border border-indigo-700/50 text-white text-sm font-semibold flex items-center gap-2 transition active:scale-95 shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.591 1.06c1.527-.917 3.293.85 2.376 2.377a1.724 1.724 0 001.06 2.591c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.06 2.591c.917 1.527-.85 3.293-2.377 2.376a1.724 1.724 0 00-2.591 1.06c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.591-1.06c-1.527.917-3.293-.85-2.376-2.377a1.724 1.724 0 00-1.06-2.591c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.06-2.591c-.917-1.527.85-3.293 2.377-2.376.918.555 2.118.038 2.591-1.06z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Settings
                        </a>
                    </div>

                    <div id="chatButtons" class="hidden flex gap-2">
                        <button id="headerVoiceCall" class="h-10 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 border border-indigo-700/50 text-white text-sm font-semibold flex items-center gap-2 transition active:scale-95 shadow-md">
                            📞 Call
                        </button>
                        <button id="headerVideoCall" class="h-10 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 border border-indigo-700/50 text-white text-sm font-semibold flex items-center gap-2 transition active:scale-95 shadow-md">
                            📹 Video
                        </button>
                    </div>
                </div>
            </div>

            <div id="chatMessages" class="flex-1 overflow-y-auto p-6 bg-slate-950/60 shadow-inner">
                <div id="emptyState" class="text-center text-slate-400 mt-20">

                </div>
            </div>

            <div id="messageContainer" class="bg-slate-900 border-t border-slate-800 p-4 flex gap-3 hidden">
                <input id="message" type="text" placeholder="Type a message..."
                    class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-600 shadow-sm">
                <button id="sendBtn" class="bg-indigo-600 hover:bg-indigo-500 border border-indigo-700/50 text-white px-6 rounded-xl transition font-semibold active:scale-95 shadow-md">
                    Send
                </button>
            </div>
        </div>
    </div>

    <div id="joinMeetingModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 backdrop-blur-sm hidden">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full mx-4 p-8 shadow-2xl">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-full bg-indigo-500/10 flex items-center justify-center border border-indigo-500/20">
                    <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white">Join a Meeting</h3>
            </div>
            <p class="text-slate-400 text-sm mb-4">Enter the meeting code or paste the full link to join.</p>
            <input type="text" id="meetingLinkInput" placeholder="Enter room code (e.g., ABC123)..."
                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 mb-4 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-600 shadow-sm">
            <div class="flex gap-3">
                <button id="joinModalCancel" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-800 text-slate-300 hover:bg-slate-800 hover:text-white transition">Cancel</button>
                <button id="joinModalSubmit" class="flex-1 px-4 py-2.5 rounded-xl bg-indigo-600 text-white hover:bg-indigo-500 border border-indigo-700/50 shadow-md transition">Join</button>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/peerjs@1.5.4/dist/peerjs.min.js"></script>
    <script>
        // ============================================================
        // READ VALUES FROM HIDDEN INPUTS
        // ============================================================
        let authUserId = document.getElementById('authUserId').value;
        let csrfToken = document.getElementById('csrfToken').value;
        let myName = document.getElementById('userName').value;

        let selectedUserId = null;
        let selectedPeerId = null;
        let unreadCounts = {};

        // DOM refs
        const chatMessages = document.getElementById('chatMessages');
        const emptyState = document.getElementById('emptyState');
        const messageInput = document.getElementById('message');
        const sendBtn = document.getElementById('sendBtn');
        const appName = document.getElementById('appName');
        const friendInfo = document.getElementById('friendInfo');
        const selectedUserDisplay = document.getElementById('selectedUser');
        const onlineStatus = document.getElementById('onlineStatus');
        const messageContainer = document.getElementById('messageContainer');
        const dashboardButtons = document.getElementById('dashboardButtons');
        const chatButtons = document.getElementById('chatButtons');
        const headerVoiceCall = document.getElementById('headerVoiceCall');
        const headerVideoCall = document.getElementById('headerVideoCall');

        // ============================================================
        // PEERJS
        // ============================================================
        let peer;
        let localStream = null;
        let currentCall = null;
        let isInCall = false;

        function initPeer() {
            peer = new Peer();
            peer.on('open', function(id) {
                console.log('MY PEER ID:', id);
                fetch('/save-peer-id', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        peer_id: id
                    })
                });
            });
            peer.on('call', function(call) {
                if (isInCall) {
                    call.close();
                    return;
                }
                const callerName = call.metadata?.name || 'Unknown';
                const callType = call.metadata?.type || 'video';
                if (confirm(callerName + ' is calling you (' + callType + '). Accept?')) {
                    acceptIncomingCall(call);
                } else {
                    call.close();
                }
            });
        }

        // ============================================================
        // INCOMING CALL
        // ============================================================
        async function acceptIncomingCall(call) {
            try {
                const type = call.metadata?.type || 'video';
                const stream = await navigator.mediaDevices.getUserMedia({
                    audio: true,
                    video: type === 'video'
                });
                localStream = stream;
                currentCall = call;
                call.answer(stream);
                call.on('stream', function(remoteStream) {
                    alert('Call connected!');
                    isInCall = true;
                });
                call.on('close', function() {
                    endCall();
                });
                await fetch('/call/accept', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
            } catch (error) {
                console.error('Accept call error:', error);
                alert('Could not accept call. Please allow camera/microphone access.');
                call.close();
            }
        }

        // ============================================================
        // INITIATE CALL
        // ============================================================
        async function initiateCall(type) {
            if (!selectedPeerId) {
                alert('Select a friend first');
                return;
            }
            if (isInCall) {
                alert('Already in a call');
                return;
            }
            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    audio: true,
                    video: type === 'video'
                });
                localStream = stream;
                const call = peer.call(selectedPeerId, stream, {
                    metadata: {
                        name: myName,
                        userId: authUserId,
                        type: type
                    }
                });
                currentCall = call;
                call.on('stream', function(remoteStream) {
                    alert('Call connected!');
                    isInCall = true;
                });
                call.on('close', function() {
                    endCall();
                });
                await fetch('/call/initiate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        callee_id: selectedUserId,
                        type: type
                    })
                });
            } catch (error) {
                console.error('Call error:', error);
                alert('Could not start call. Please allow camera/microphone access.');
            }
        }

        function endCall() {
            if (currentCall) currentCall.close();
            if (localStream) localStream.getTracks().forEach(t => t.stop());
            isInCall = false;
            currentCall = null;
            localStream = null;
        }

        // ============================================================
        // ECHO LISTENERS FOR CHAT
        // ============================================================
        function initEchoListeners() {
            if (typeof window.Echo === 'undefined') {
                console.warn('Echo not loaded');
                return;
            }
            window.Echo.private('user.' + authUserId)
                .listen('NewMessage', function(data) {
                    console.log('📩 New message:', data);
                    if (data.sender_id == selectedUserId) {
                        appendMessage(data);
                    } else {
                        const badge = document.querySelector('.unread-badge[data-user-id="' + data.sender_id + '"]');
                        if (badge) {
                            badge.classList.remove('hidden');
                            if (!unreadCounts[data.sender_id]) unreadCounts[data.sender_id] = 0;
                            unreadCounts[data.sender_id]++;
                            badge.querySelector('span').textContent = unreadCounts[data.sender_id];
                        }
                        const lastMsg = document.querySelector('.last-message[data-user-id="' + data.sender_id + '"]');
                        if (lastMsg) {
                            lastMsg.textContent = data.sender_name + ': ' + data.content;
                        }
                    }
                });
            console.log('✅ Echo listeners initialized');
        }

        // ============================================================
        // CHAT FUNCTIONS
        // ============================================================
        function selectUser(userId, peerId, name) {
            if (!userId || !peerId) return;
            selectedUserId = userId;
            selectedPeerId = peerId;

            emptyState.style.display = 'none';
            appName.classList.add('hidden');
            selectedUserDisplay.textContent = name;
            onlineStatus.textContent = 'Online';
            friendInfo.classList.remove('hidden');

            // SWITCH BUTTONS: Hide Host+Join, Show Call+Video
            dashboardButtons.classList.add('hidden');
            chatButtons.classList.remove('hidden');

            messageContainer.classList.remove('hidden');
            messageInput.disabled = false;
            messageInput.placeholder = 'Type a message...';
            sendBtn.disabled = false;

            loadMessages(userId);

            const badge = document.querySelector('.unread-badge[data-user-id="' + userId + '"]');
            if (badge) {
                badge.classList.add('hidden');
                delete unreadCounts[userId];
            }
        }

        async function loadMessages(userId) {
            if (!userId) return;
            try {
                const response = await fetch('/messages/' + userId);
                if (!response.ok) throw new Error('HTTP ' + response.status);
                const messages = await response.json();
                chatMessages.innerHTML = '';
                if (messages.length === 0) {
                    const noMsg = document.createElement('div');
                    noMsg.className = 'text-center text-slate-400 mt-20';
                    noMsg.innerHTML = '<p class="text-4xl mb-2">💬</p><p>No messages yet. Say hello!</p>';
                    chatMessages.appendChild(noMsg);
                }
                messages.forEach(msg => appendMessage(msg));
                chatMessages.scrollTop = chatMessages.scrollHeight;
                await fetch('/messages/read/' + userId, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
            } catch (error) {
                console.error('Error loading messages:', error);
            }
        }

        function appendMessage(msg) {
            const isMine = msg.sender_id == authUserId;
            const div = document.createElement('div');
            div.className = 'flex ' + (isMine ? 'justify-end' : 'justify-start') + ' mb-3';
            div.innerHTML = `
                <div class="${isMine ? 'bg-blue-500 text-white' : 'bg-[#111c31] text-slate-100'} rounded-2xl px-4 py-2 max-w-[70%] break-words">
                    ${!isMine ? '<div class="text-xs font-semibold text-slate-400 mb-1">' + (msg.sender_name || 'User') + '</div>' : ''}
                    <div>${msg.content}</div>
                    <div class="text-xs ${isMine ? 'text-blue-200' : 'text-slate-400'} mt-1 text-right">${msg.created_at || 'Just now'}</div>
                </div>
            `;
            chatMessages.appendChild(div);
            chatMessages.scrollTop = chatMessages.scrollHeight;
            const senderId = isMine ? msg.receiver_id : msg.sender_id;
            const lastMsg = document.querySelector('.last-message[data-user-id="' + senderId + '"]');
            if (lastMsg) {
                lastMsg.textContent = (isMine ? 'You: ' : '') + msg.content;
            }
        }

        async function sendMessage() {
            const content = messageInput.value.trim();
            if (!content || !selectedUserId) return;
            try {
                const response = await fetch('/messages', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        receiver_id: selectedUserId,
                        content: content
                    })
                });
                const data = await response.json();
                if (data.success) {
                    messageInput.value = '';
                    const noMsg = chatMessages.querySelector('.text-center.text-slate-400.mt-20');
                    if (noMsg) noMsg.remove();
                    appendMessage({
                        sender_id: parseInt(authUserId),
                        sender_name: myName,
                        content: content,
                        created_at: new Date().toLocaleTimeString()
                    });
                }
            } catch (error) {
                console.error('Error sending message:', error);
            }
        }

        // ============================================================
        // SEARCH FRIENDS
        // ============================================================
        const searchInput = document.getElementById('searchUsers');
        if (searchInput) {
            searchInput.addEventListener('keyup', function(e) {
                const query = e.target.value.toLowerCase();
                document.querySelectorAll('.user-item').forEach(function(el) {
                    const name = el.querySelector('h3').textContent.toLowerCase();
                    el.style.display = name.includes(query) ? '' : 'none';
                });
            });
        }

        // ============================================================
        // CALL BUTTONS
        // ============================================================
        headerVoiceCall.addEventListener('click', function() {
            if (selectedUserId) initiateCall('audio');
        });
        headerVideoCall.addEventListener('click', function() {
            if (selectedUserId) initiateCall('video');
        });

        // ============================================================
        // JOIN MEETING MODAL
        // ============================================================
        document.getElementById('joinMeetingBtn').addEventListener('click', function() {
            document.getElementById('joinMeetingModal').classList.remove('hidden');
            document.getElementById('meetingLinkInput').value = '';
            document.getElementById('meetingLinkInput').focus();
        });

        document.getElementById('joinModalCancel').addEventListener('click', function() {
            document.getElementById('joinMeetingModal').classList.add('hidden');
        });

        document.getElementById('joinModalSubmit').addEventListener('click', function() {
            const input = document.getElementById('meetingLinkInput').value.trim();
            if (!input) {
                alert('Please enter a room code or link');
                return;
            }
            document.getElementById('joinMeetingModal').classList.add('hidden');
            let roomId = input;
            if (input.includes('/meeting/')) {
                const parts = input.split('/meeting/');
                roomId = parts[parts.length - 1].trim();
            }
            window.location.href = '/meeting/' + roomId;
        });

        document.getElementById('meetingLinkInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('joinModalSubmit').click();
            }
        });

        document.getElementById('joinMeetingModal').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });

        // ============================================================
        // EVENT LISTENERS
        // ============================================================
        document.addEventListener('DOMContentLoaded', function() {
            initPeer();
            initEchoListeners();

            document.querySelectorAll('.user-item').forEach(function(el) {
                el.addEventListener('click', function(e) {
                    const userId = this.dataset.userId;
                    const peerId = this.dataset.peerId;
                    const name = this.querySelector('h3').textContent;
                    if (userId && peerId) selectUser(userId, peerId, name);
                });
            });

            sendBtn.addEventListener('click', sendMessage);
            messageInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') sendMessage();
            });

            console.log('✅ Dashboard ready');
        });
    </script>
</x-app-layout>