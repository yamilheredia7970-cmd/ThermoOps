import React, { useState, useEffect } from 'react';
import { AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';
import { cn } from '../lib/utils';
import { formatCurrency } from '../lib/utils';
import { Card, CardHeader, CardTitle, CardContent, Badge, Button } from '../components/ui';
import { Skeleton, SkeletonCard, SkeletonTable } from '../components/ui/Skeleton';
import { ActivityFeed } from '../components/ActivityFeed';
import { mockWorkOrders, mockActivities } from '../data/mockData';
import { 
  ClipboardList, 
  Wrench, 
  DollarSign, 
  ArrowUpRight, 
  AlertTriangle, 
  BatteryWarning, 
  Clock 
} from 'lucide-react';

const chartData = [
  { name: 'Mon', completed: 12, scheduled: 15 },
  { name: 'Tue', completed: 18, scheduled: 16 },
  { name: 'Wed', completed: 15, scheduled: 20 },
  { name: 'Thu', completed: 22, scheduled: 20 },
  { name: 'Fri', completed: 19, scheduled: 18 },
  { name: 'Sat', completed: 8, scheduled: 5 },
  { name: 'Sun', completed: 3, scheduled: 2 },
];

export function Dashboard() {
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState<'overview' | 'schedule'>('overview');

  useEffect(() => {
    const timer = setTimeout(() => setLoading(false), 800);
    return () => clearTimeout(timer);
  }, []);

  const activeWorkOrders = mockWorkOrders.filter(wo => wo.status === 'In Progress' || wo.status === 'Scheduled').length;
  const activeTechs = 4; // Mock value

  if (loading) {
    return (
      <div className="space-y-6">
        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <h1 className="text-2xl font-bold text-surface-900">Operations Dashboard</h1>
      </div>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <SkeletonCard />
          <SkeletonCard />
          <SkeletonCard />
        </div>
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <Skeleton className="lg:col-span-2 h-[380px] rounded-xl" />
          <Skeleton className="h-[380px] rounded-xl" />
        </div>
        <SkeletonTable rows={4} />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <h1 className="text-2xl font-bold text-surface-900">Operations Dashboard</h1>
        <div className="flex bg-surface-200 p-1 rounded-lg">
           <button onClick={() => setActiveTab('overview')} className={cn("px-4 py-1.5 text-sm font-medium rounded-md transition-colors", activeTab === 'overview' ? "bg-white text-surface-900 shadow-sm" : "text-surface-600 hover:text-surface-900")}>Overview</button>
           <button onClick={() => setActiveTab('schedule')} className={cn("px-4 py-1.5 text-sm font-medium rounded-md transition-colors", activeTab === 'schedule' ? "bg-white text-surface-900 shadow-sm" : "text-surface-600 hover:text-surface-900")}>Today's Schedule</button>
        </div>
      </div>

      {activeTab === 'overview' && (
        <>
      
      {/* Metrics Row */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card>
          <CardContent className="p-5 flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-surface-500 mb-1">Active Work Orders</p>
              <h3 className="text-2xl font-bold text-surface-900">{activeWorkOrders}</h3>
              <p className="text-xs text-surface-500 mt-1">
                2 requires immediate attention
              </p>
            </div>
            <div className="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center text-primary-600">
              <ClipboardList className="w-6 h-6" />
            </div>
          </CardContent>
        </Card>
        
        <Card>
          <CardContent className="p-5 flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-surface-500 mb-1">Technicians Available</p>
              <h3 className="text-2xl font-bold text-surface-900">3 / 7</h3>
              <p className="text-xs text-surface-500 mt-1">
                {activeTechs} currently in the field
              </p>
            </div>
            <div className="w-12 h-12 bg-warning-100 rounded-xl flex items-center justify-center text-warning-600">
              <Wrench className="w-6 h-6" />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-5 flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-surface-500 mb-1">Monthly Revenue</p>
              <h3 className="text-2xl font-bold text-surface-900">{formatCurrency(48750)}</h3>
              <p className="text-xs text-success-700 flex items-center mt-1 font-medium">
                <ArrowUpRight className="w-3 h-3 mr-1" /> +8.4% vs last month
              </p>
            </div>
            <div className="w-12 h-12 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
              <DollarSign className="w-6 h-6" />
            </div>
          </CardContent>
        </Card>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Service Activity Chart */}
        <Card className="lg:col-span-2">
          <CardHeader>
            <CardTitle>Service Activity</CardTitle>
            <div className="flex bg-surface-100 p-1 rounded-lg">
              <button className="px-3 py-1 text-xs font-medium bg-white shadow-sm rounded-md text-surface-900">7 Days</button>
              <button className="px-3 py-1 text-xs font-medium text-surface-500 hover:text-surface-900">30 Days</button>
            </div>
          </CardHeader>
          <CardContent>
            <div className="h-[280px] w-full">
              <ResponsiveContainer width="100%" height="100%">
                <AreaChart data={chartData} margin={{ top: 10, right: 10, left: -20, bottom: 0 }}>
                  <defs>
                    <linearGradient id="colorCompleted" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="5%" stopColor="var(--color-primary-500)" stopOpacity={0.3}/>
                      <stop offset="95%" stopColor="var(--color-primary-500)" stopOpacity={0}/>
                    </linearGradient>
                  </defs>
                  <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="var(--color-surface-200)" />
                  <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{fill: 'var(--color-surface-500)', fontSize: 12}} dy={10} />
                  <YAxis axisLine={false} tickLine={false} tick={{fill: 'var(--color-surface-500)', fontSize: 12}} />
                  <Tooltip 
                    contentStyle={{ borderRadius: '8px', border: 'none', boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)' }}
                    itemStyle={{ fontSize: '13px', fontWeight: 500 }}
                  />
                  <Area type="monotone" dataKey="completed" name="Completed" stroke="var(--color-primary-500)" strokeWidth={3} fillOpacity={1} fill="url(#colorCompleted)" />
                  <Area type="monotone" dataKey="scheduled" name="Scheduled" stroke="var(--color-surface-300)" strokeWidth={2} strokeDasharray="4 4" fill="none" />
                </AreaChart>
              </ResponsiveContainer>
            </div>
          </CardContent>
        </Card>

        {/* Activity Feed */}
        <ActivityFeed activities={mockActivities} className="h-full flex flex-col" />
      </div>

        </>
      )}

      {activeTab === 'schedule' && (
        <>
      {/* Today's Schedule (Mini Dispatch) */}
      <Card>
        <CardHeader>
          <CardTitle>Today's Active Schedule</CardTitle>
          <Button variant="ghost" size="sm">View Full Board</Button>
        </CardHeader>
        <div className="hidden md:block overflow-x-auto">
          <table className="w-full text-sm text-left">
            <thead className="text-xs text-surface-500 uppercase bg-surface-50 border-y border-surface-200">
              <tr>
                <th className="px-5 py-3 font-semibold">Time</th>
                <th className="px-5 py-3 font-semibold">Work Order</th>
                <th className="px-5 py-3 font-semibold">Customer / Location</th>
                <th className="px-5 py-3 font-semibold">Technician</th>
                <th className="px-5 py-3 font-semibold">Status</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-surface-100">
              {mockWorkOrders.map((wo) => {
                const isOverdue = wo.status === 'Scheduled' && parseInt(wo.scheduledTime) < 10; // Mock overdue logic
                return (
                  <tr key={wo.id} className="hover:bg-surface-50 transition-colors">
                    <td className="px-5 py-4 whitespace-nowrap font-medium text-surface-900">
                      {wo.scheduledTime}
                    </td>
                    <td className="px-5 py-4">
                      <div className="font-semibold text-primary-600 hover:underline cursor-pointer">{wo.id}</div>
                      <div className="text-xs text-surface-500 mt-0.5">{wo.serviceType}</div>
                    </td>
                    <td className="px-5 py-4">
                      <div className="font-medium text-surface-900">{wo.customerName}</div>
                      <div className="text-xs text-surface-500 mt-0.5">{wo.locationName}</div>
                    </td>
                    <td className="px-5 py-4">
                      <div className="flex items-center gap-2">
                        <div className="w-6 h-6 rounded-full bg-surface-200 flex items-center justify-center text-[10px] font-bold text-surface-600">
                          {wo.technicianName?.split(' ').map(n => n[0]).join('') || '?'}
                        </div>
                        <span className="font-medium">{wo.technicianName || 'Unassigned'}</span>
                      </div>
                    </td>
                    <td className="px-5 py-4">
                      <Badge variant={
                        wo.status === 'Completed' ? 'success' : 
                        wo.status === 'In Progress' ? 'info' : 
                        isOverdue ? 'error' : 'default'
                      }>
                        {isOverdue ? 'Overdue' : wo.status}
                      </Badge>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>

        {/* Mobile Vertical Cards View */}
        <div className="md:hidden flex flex-col gap-4 p-4 pt-0">
          {mockWorkOrders.map((wo) => {
            const isOverdue = wo.status === 'Scheduled' && parseInt(wo.scheduledTime) < 10;
            return (
              <div key={wo.id + '_mobile'} className="border border-surface-200 rounded-xl p-4 bg-white shadow-sm flex flex-col gap-3">
                <div className="flex justify-between items-start">
                  <div>
                    <h4 className="font-bold text-primary-600 cursor-pointer">{wo.id}</h4>
                    <p className="text-sm font-medium text-surface-900">{wo.serviceType}</p>
                  </div>
                  <Badge variant={
                    wo.status === 'Completed' ? 'success' : 
                    wo.status === 'In Progress' ? 'info' : 
                    isOverdue ? 'error' : 'default'
                  }>
                    {isOverdue ? 'Overdue' : wo.status}
                  </Badge>
                </div>
                <div className="text-sm text-surface-500 flex flex-col gap-2">
                  <div className="flex items-center gap-2">
                     <span className="font-semibold text-surface-700">{wo.scheduledTime}</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <span className="font-medium text-surface-900">{wo.customerName}</span>
                    <span className="text-xs">• {wo.locationName}</span>
                  </div>
                </div>
                <Button variant="outline" className="w-full mt-2 bg-surface-50" size="sm">Quick Update</Button>
              </div>
            );
          })}
        </div>
      </Card>
        </>
      )}
    </div>
  );
}
