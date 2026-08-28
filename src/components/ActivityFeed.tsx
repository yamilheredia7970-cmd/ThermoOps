import React from 'react';
import { Activity } from '../types';
import { Card } from './ui';
import { ClipboardList, Wind, Users, Settings, Clock } from 'lucide-react';
import { formatDistanceToNow } from 'date-fns';

interface ActivityFeedProps {
  activities: Activity[];
  className?: string;
  title?: string;
}

const typeIcons = {
  WorkOrder: <ClipboardList className="w-4 h-4 text-primary-600" />,
  Equipment: <Wind className="w-4 h-4 text-accent-600" />,
  Customer: <Users className="w-4 h-4 text-success-600" />,
  System: <Settings className="w-4 h-4 text-surface-500" />,
};

const typeColors = {
  WorkOrder: 'bg-primary-50 border-primary-100',
  Equipment: 'bg-accent-50 border-accent-100',
  Customer: 'bg-success-50 border-success-100',
  System: 'bg-surface-50 border-surface-200',
};

export function ActivityFeed({ activities, className, title = "Recent Activity" }: ActivityFeedProps) {
  return (
    <Card className={className}>
      <div className="p-5 border-b border-surface-100 flex items-center justify-between">
        <h3 className="font-bold text-surface-900 flex items-center gap-2">
          <Clock className="w-5 h-5 text-surface-400" />
          {title}
        </h3>
      </div>
      <div className="p-0">
        {activities.length > 0 ? (
          <div className="divide-y divide-surface-100">
            {activities.map((activity) => (
              <div key={activity.id} className="p-4 flex gap-4 hover:bg-surface-50 transition-colors">
                <div className={`w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center border ${typeColors[activity.type]}`}>
                  {typeIcons[activity.type]}
                </div>
                <div className="flex-1 min-w-0">
                  <p className="text-sm font-semibold text-surface-900">{activity.title}</p>
                  <p className="text-sm text-surface-600 mt-1 line-clamp-2">{activity.description}</p>
                  <div className="flex items-center gap-2 mt-2 text-xs text-surface-400">
                    <span>{new Date(activity.timestamp).toLocaleString()}</span>
                    {activity.actor && (
                      <>
                        <span>•</span>
                        <span>{activity.actor}</span>
                      </>
                    )}
                  </div>
                </div>
              </div>
            ))}
          </div>
        ) : (
          <div className="p-8 text-center text-surface-500 text-sm">
            No recent activity found.
          </div>
        )}
      </div>
    </Card>
  );
}
