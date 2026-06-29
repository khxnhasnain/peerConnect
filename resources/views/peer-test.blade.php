<!DOCTYPE html>
<html>

<head>
    <title>PeerJS Voice Call</title>
</head>

<body>

    <h2>My Peer ID</h2>

    <div id="myId">Loading...</div>

    <hr>

    <input
        type="text"
        id="peerId"
        placeholder="Enter Peer ID">

    <button onclick="connectPeer()">
        Connect
    </button>

    <button onclick="callPeer()">
        Voice Call
    </button>

    <button onclick="videoCallPeer()">
        Video Call
    </button>

    <hr>

    <input
        type="text"
        id="message"
        placeholder="Message">

    <button onclick="sendMessage()">
        Send
    </button>

    <hr>

    <div id="chat"></div>

    <hr>

    <h3>My Video</h3>
    <video id="localVideo"
        autoplay
        muted
        playsinline
        width="300">
    </video>

    <h3>Remote Video</h3>
    <video id="remoteVideo"
        autoplay
        playsinline
        width="300">
    </video>

    <hr>

    <div id="incomingCall"
        style="
        display:none;
        border:1px solid black;
        padding:20px;
        width:250px;
     ">

        <h3>Incoming Call</h3>

        <button onclick="acceptCall()">
            Accept
        </button>

        <button onclick="rejectCall()">
            Reject
        </button>

    </div>

    <script src="https://unpkg.com/peerjs@1.5.4/dist/peerjs.min.js"></script>

    <script>
        let conn;
        let localStream;
        let incomingCall;

        const peer = new Peer();

        peer.on('open', function(id) {

            document.getElementById('myId')
                .innerHTML = id;

        });

        /* CHAT CONNECTION */

        function connectPeer() {
            let peerId =
                document.getElementById('peerId').value;

            conn = peer.connect(peerId);

            conn.on('open', function() {

                alert("Connected");

            });

            conn.on('data', function(data) {

                document.getElementById('chat')
                    .innerHTML +=
                    "<p>Friend: " + data + "</p>";

            });
        }

        peer.on('connection', function(connection) {

            conn = connection;

            conn.on('data', function(data) {

                document.getElementById('chat')
                    .innerHTML +=
                    "<p>Friend: " + data + "</p>";

            });

        });

        /* SEND MESSAGE */

        function sendMessage() {
            let msg =
                document.getElementById('message').value;

            conn.send(msg);

            document.getElementById('chat')
                .innerHTML +=
                "<p>Me: " + msg + "</p>";
        }

        /* CALL USER */

        async function callPeer() {
            let peerId =
                document.getElementById('peerId').value;

            if (!peerId) {
                alert("Enter Peer ID");

                return;
            }

            try {
                localStream =
                    await navigator.mediaDevices.getUserMedia({
                        audio: true
                    });

                const call =
                    peer.call(peerId, localStream);

                call.on('stream',
                    function(remoteStream) {

                        const audio =
                            new Audio();

                        audio.srcObject =
                            remoteStream;

                        audio.play();

                    });

                call.on('close',
                    function() {

                        alert("Call Ended");

                    });

            } catch (error) {
                console.log(error);

                alert(
                    "Microphone permission denied."
                );
            }
        }

        async function videoCallPeer() {
            let peerId =
                document.getElementById('peerId').value;

            if (!peerId) {
                alert("Enter Peer ID");
                return;
            }

            try {
                localStream =
                    await navigator.mediaDevices.getUserMedia({
                        audio: true,
                        video: true
                    });

                document
                    .getElementById('localVideo')
                    .srcObject = localStream;

                const call =
                    peer.call(peerId, localStream);

                call.on('stream',
                    function(remoteStream) {

                        document
                            .getElementById('remoteVideo')
                            .srcObject = remoteStream;

                    });

            } catch (error) {
                console.log(error);
            }
        }

        /* RECEIVE CALL */

        peer.on('call', function(call) {

            incomingCall = call;

            document.getElementById(
                'incomingCall'
            ).style.display = 'block';

        });

        /* ACCEPT CALL */

        async function acceptCall() {
            try {
                localStream =
                    await navigator.mediaDevices.getUserMedia({
                        audio: true,
                        video: true
                    });

                document
                    .getElementById('localVideo')
                    .srcObject = localStream;

                incomingCall.answer(
                    localStream
                );
                incomingCall.on(
                    'stream',
                    function(remoteStream) {

                        document
                            .getElementById('remoteVideo')
                            .srcObject = remoteStream;

                    }
                );

                document.getElementById(
                    'incomingCall'
                ).style.display = 'none';
            } catch (error) {
                console.log(error);

                alert(
                    "Microphone permission denied."
                );
            }
        }

        /* REJECT CALL */

        function rejectCall() {
            incomingCall.close();

            document.getElementById(
                'incomingCall'
            ).style.display = 'none';
        }
    </script>

</body>

</html>