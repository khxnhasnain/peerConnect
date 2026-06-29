import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Add this ↓
window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY || '7f08a724509d8dee9edf',
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'ap2',
    forceTLS: true,
});

console.log('✅ Echo ready');