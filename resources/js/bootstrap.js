import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
});

const userId = document.head.querySelector('meta[name="user-id"]').content;

if (userId) {
    Echo.channel(`seller-status-${userId}`)
        .listen('.SellerStatusUpdated', (e) => {
            console.log('📢 Notification:', e.message);
            alert(e.message); // Or show toast
        });
}
