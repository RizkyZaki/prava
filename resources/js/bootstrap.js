import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Enable Pusher logging for debugging
Pusher.logToConsole = true;

// Read Reverb config from server-injected meta tags (always correct on production)
// Falls back to Vite env vars (baked at build time)
const getMeta = (name, fallback) => {
    const el = document.querySelector(`meta[name="reverb-${name}"]`);
    return (el && el.content) ? el.content : fallback;
};

const reverbKey = getMeta('key', import.meta.env.VITE_REVERB_APP_KEY);
const reverbHost = getMeta('host', import.meta.env.VITE_REVERB_HOST);
const reverbPort = getMeta('port', import.meta.env.VITE_REVERB_PORT) || 80;
const reverbScheme = getMeta('scheme', import.meta.env.VITE_REVERB_SCHEME) || 'https';

try {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: reverbKey,
        wsHost: reverbHost,
        wsPort: reverbPort,
        wssPort: reverbPort,
        forceTLS: reverbScheme === 'https',
        enabledTransports: ['ws', 'wss'],
    });

    console.log('[Echo] ✅ Laravel Echo initialized', {
        key: reverbKey,
        host: reverbHost,
        port: reverbPort,
        scheme: reverbScheme,
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
