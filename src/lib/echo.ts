import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Reverb speaks the Pusher protocol; Echo's "reverb" broadcaster expects
// Pusher to be available as a global, same as it would be with a <script> tag.
(window as unknown as { Pusher: typeof Pusher }).Pusher = Pusher;

function readCookie(name: string): string | null {
  const match = document.cookie.match(new RegExp(`(?:^|; )${name}=([^;]*)`));
  return match ? decodeURIComponent(match[1]) : null;
}

/**
 * Custom authorizer instead of Echo's built-in `authEndpoint` + static auth
 * header: the XSRF-TOKEN cookie can rotate between page load and the moment
 * a channel is actually subscribed to, so it needs to be read fresh on each
 * authorization request rather than captured once at Echo construction time.
 */
interface ChannelAuthorizationData {
  auth: string;
  channel_data?: string;
  shared_secret?: string;
}

function sanctumChannelAuthorizer(channel: { name: string }) {
  return {
    authorize(socketId: string, callback: (error: Error | null, data: ChannelAuthorizationData | null) => void) {
      fetch('/api/broadcasting/auth', {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-XSRF-TOKEN': readCookie('XSRF-TOKEN') ?? '',
        },
        body: JSON.stringify({ socket_id: socketId, channel_name: channel.name }),
      })
        .then(res => (res.ok ? res.json() : Promise.reject(new Error(`Channel auth failed: ${res.status}`))))
        .then(data => callback(null, data))
        .catch(error => callback(error instanceof Error ? error : new Error(String(error)), null));
    },
  };
}

let echoInstance: Echo<'reverb'> | null = null;

/** Lazily creates a single shared Echo connection for the whole app. */
export function getEcho(): Echo<'reverb'> {
  if (!echoInstance) {
    echoInstance = new Echo({
      broadcaster: 'reverb',
      key: import.meta.env.VITE_REVERB_APP_KEY,
      wsHost: import.meta.env.VITE_REVERB_HOST,
      wsPort: Number(import.meta.env.VITE_REVERB_PORT ?? 8080),
      wssPort: Number(import.meta.env.VITE_REVERB_PORT ?? 8080),
      forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
      enabledTransports: ['ws', 'wss'],
      authorizer: sanctumChannelAuthorizer,
    });
  }

  return echoInstance;
}

export function disconnectEcho(): void {
  echoInstance?.disconnect();
  echoInstance = null;
}
