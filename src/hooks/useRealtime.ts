import { useEffect } from 'react';
import { useAuth } from '../contexts/AuthContext';
import { getEcho } from '../lib/echo';
import { Activity } from '../types';

interface DispatchBoardHandlers {
  onWorkOrderSaved?: () => void;
  onTechnicianStatusChanged?: () => void;
  onInventoryLowStock?: () => void;
  onActivityLogged?: (activity: Activity) => void;
}

/**
 * Subscribes to the private 'dispatch-board' channel for the lifetime of the
 * component. Admin/Dispatcher only, matching routes/channels.php on the
 * backend - silently does nothing for any other role rather than attempting
 * (and failing) a subscription the server would reject anyway.
 */
export function useDispatchBoardChannel(handlers: DispatchBoardHandlers): void {
  const { hasRole } = useAuth();
  const { onWorkOrderSaved, onTechnicianStatusChanged, onInventoryLowStock, onActivityLogged } = handlers;

  useEffect(() => {
    if (!hasRole('Admin', 'Dispatcher')) return;

    const channel = getEcho().private('dispatch-board');

    if (onWorkOrderSaved) channel.listen('.work-order.saved', onWorkOrderSaved);
    if (onTechnicianStatusChanged) channel.listen('.technician.status-changed', onTechnicianStatusChanged);
    if (onInventoryLowStock) channel.listen('.inventory.low-stock', onInventoryLowStock);
    if (onActivityLogged) channel.listen('.activity.logged', onActivityLogged);

    return () => {
      getEcho().leave('dispatch-board');
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [hasRole('Admin', 'Dispatcher')]);
}

interface NotificationPayload {
  id: string;
  title: string;
  body: string;
  [key: string]: unknown;
}

/**
 * Subscribes to the user's own default private channel (App.Models.User.{id})
 * for live Notification broadcasts - laravel-echo's `.notification()` helper
 * listens for Illuminate's BroadcastNotificationCreated event under the hood.
 */
export function useUserNotificationsChannel(onNotification: (notification: NotificationPayload) => void): void {
  const { user } = useAuth();

  useEffect(() => {
    if (!user) return;

    const channelName = `App.Models.User.${user.id}`;
    const channel = getEcho().private(channelName);
    channel.notification(onNotification);

    return () => {
      getEcho().leave(channelName);
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [user?.id]);
}
