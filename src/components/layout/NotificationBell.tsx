import React, { useEffect, useRef, useState } from 'react';
import { Bell } from 'lucide-react';
import { formatDistanceToNow } from 'date-fns';
import { api } from '../../lib/api';
import { useApiList } from '../../hooks/useApi';
import { useUserNotificationsChannel } from '../../hooks/useRealtime';

interface AppNotification {
  id: string;
  title: string;
  body: string;
  readAt: string | null;
  createdAt: string;
}

export function NotificationBell() {
  const [isOpen, setIsOpen] = useState(false);
  const [unreadCount, setUnreadCount] = useState(0);
  const containerRef = useRef<HTMLDivElement>(null);
  const { data: notifications, reload } = useApiList<AppNotification>('/notifications?per_page=10');

  const refreshUnreadCount = () => {
    api
      .get<{ count: number }>('/notifications/unread-count')
      .then(res => setUnreadCount(res.count))
      .catch(() => {});
  };

  useEffect(() => {
    refreshUnreadCount();
  }, []);

  useUserNotificationsChannel(() => {
    reload();
    refreshUnreadCount();
  });

  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      if (containerRef.current && !containerRef.current.contains(event.target as Node)) {
        setIsOpen(false);
      }
    }
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const markAsRead = async (id: string) => {
    await api.post(`/notifications/${id}/read`);
    reload();
    refreshUnreadCount();
  };

  const markAllAsRead = async () => {
    await api.post('/notifications/read-all');
    reload();
    refreshUnreadCount();
  };

  return (
    <div className="relative" ref={containerRef}>
      <button
        onClick={() => setIsOpen(open => !open)}
        className="relative p-2 text-surface-500 hover:text-surface-900 transition-colors rounded-lg hover:bg-surface-50"
      >
        <Bell className="w-5 h-5" />
        {unreadCount > 0 && (
          <span className="absolute top-1.5 right-1.5 w-2 h-2 bg-error-500 rounded-full border-2 border-white"></span>
        )}
      </button>

      {isOpen && (
        <div className="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-surface-200 z-30 max-h-96 overflow-y-auto">
          <div className="p-3 border-b border-surface-100 flex items-center justify-between">
            <h3 className="font-bold text-sm text-surface-900">Notifications</h3>
            {unreadCount > 0 && (
              <button onClick={markAllAsRead} className="text-xs text-primary-600 hover:text-primary-800 font-medium">
                Mark all read
              </button>
            )}
          </div>
          {notifications && notifications.length > 0 ? (
            <div className="divide-y divide-surface-100">
              {notifications.map(notification => (
                <button
                  key={notification.id}
                  onClick={() => !notification.readAt && markAsRead(notification.id)}
                  className={`w-full text-left p-3 hover:bg-surface-50 transition-colors ${!notification.readAt ? 'bg-primary-50/50' : ''}`}
                >
                  <p className="text-sm font-semibold text-surface-900">{notification.title}</p>
                  <p className="text-xs text-surface-600 mt-0.5 line-clamp-2">{notification.body}</p>
                  <p className="text-[10px] text-surface-400 mt-1">
                    {formatDistanceToNow(new Date(notification.createdAt), { addSuffix: true })}
                  </p>
                </button>
              ))}
            </div>
          ) : (
            <div className="p-6 text-center text-sm text-surface-500">No notifications yet.</div>
          )}
        </div>
      )}
    </div>
  );
}
