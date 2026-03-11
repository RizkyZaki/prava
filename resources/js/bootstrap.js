import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Enable Pusher logging for debugging
Pusher.logToConsole = true;

try {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
    });

    console.log('[Echo] ✅ Laravel Echo initialized', {
        key: import.meta.env.VITE_REVERB_APP_KEY,
        host: import.meta.env.VITE_REVERB_HOST,
        port: import.meta.env.VITE_REVERB_PORT,
        scheme: import.meta.env.VITE_REVERB_SCHEME,
    });

    window.Echo.connector.pusher.connection.bind('connected', () => {
        console.log('[Echo] ✅ WebSocket CONNECTED to Reverb');
    });
    window.Echo.connector.pusher.connection.bind('error', (err) => {
        console.error('[Echo] ❌ WebSocket ERROR:', err);
    });
    window.Echo.connector.pusher.connection.bind('disconnected', () => {
        console.warn('[Echo] ⚠️ WebSocket DISCONNECTED');
    });
    window.Echo.connector.pusher.connection.bind('state_change', (states) => {
        console.log('[Echo] 🔄 State change:', states.previous, '→', states.current);
    });
} catch (e) {
    console.error('[Echo] ❌ Failed to initialize Laravel Echo:', e);
    window.Echo = null;
}
