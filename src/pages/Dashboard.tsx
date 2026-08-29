import React, { useState } from 'react';
import { AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';
import { cn } from '../lib/utils';
import { formatCurrency } from '../lib/utils';
import { Card, CardHeader, CardTitle, CardContent, Badge } from '../components/ui';
import { Skeleton, SkeletonCard, SkeletonTable } from '../components/ui/Skeleton';
import { ActivityFeed } from '../components/ActivityFeed';
import { useApiResource } from '../hooks/useApi';
import { WorkOrder } from '../types';
import {
  ClipboardList,
  Wrench,
  DollarSign,
  ArrowUpRight,
} from 'lucide-react';

interface DashboardData {
  activeWorkOrders: number;
  technicians: { available: number; total: number; inField: number };
  monthlyRevenue: number;
  serviceActivity: { date: string; day: string; completed: number; scheduled: number }[];
  todaysSchedule: WorkOrder[];
}

export function Dashboard() {
  const { data, loading } = useApiResource<DashboardData>('/dashboard');
  const [activeTab, setActiveTab] = useState<'overview' | 'schedule'>('overview');

  if (loading || !data) {
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
              <h3 className="text-2xl font-bold text-surface-900">{data.activeWorkOrders}</h3>
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
              <h3 className="text-2xl font-bold text-surface-900">{data.technicians.available} / {data.technicians.total}</h3>
              <p className="text-xs text-surface-500 mt-1">
                {data.technicians.inField} currently in the field
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
              <h3 className="text-2xl font-bold text-surface-900">{formatCurrency(data.monthlyRevenue)}</h3>
              <p className="text-xs text-success-700 flex items-center mt-1 font-medium">
                <ArrowUpRight className="w-3 h-3 mr-1" /> From completed jobs this month
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
            </div>
          </CardHeader>
          <CardContent>
            <div className="h-[280px] w-full">
              <ResponsiveContainer width="100%" height="100%">
                <AreaChart data={data.serviceActivity} margin={{ top: 10, right: 10, left: -20, bottom: 0 }}>
                  <defs>
                    <linearGradient id="colorCompleted" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="5%" stopColor="var(--color-primary-500)" stopOpacity={0.3}/>
                      <stop offset="95%" stopColor="var(--color-primary-500)" stopOpacity={0}/>
                    </linearGradient>
                  </defs>
                  <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="var(--color-surface-200)" />
                  <XAxis dataKey="day" axisLine={false} tickLine={false} tick={{fill: 'var(--color-surface-500)', fontSize: 12}} dy={10} />
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
        {/* Real activity feed isn't wired up yet (no Activities API); shown empty rather than stale mock data. */}
        <ActivityFeed activities={[]} className="h-full flex flex-col" />
      </div>

        </>
      )}

      {activeTab === 'schedule' && (
        <>
      {/* Today's Schedule (Mini Dispatch) */}
      <Card>
        <CardHeader>
          <CardTitle>Today's Active Schedule</CardTitle>
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
              {data.todaysSchedule.map((wo) => (
                  <tr key={wo.id} className="hover:bg-surface-50 transition-colors">
                    <td className="px-5 py-4 whitespace-nowrap font-medium text-surface-900">
                      {wo.scheduledTime}
                    </td>
                    <td className="px-5 py-4">
                      <div className="font-semibold text-primary-600 hover:underline cursor-pointer">WO-{wo.id}</div>
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
                        wo.status === 'In Progress' ? 'info' : 'default'
                      }>
                        {wo.status}
                      </Badge>
                    </td>
                  </tr>
                )
              )}
            </tbody>
          </table>
        </div>

        {/* Mobile Vertical Cards View */}
        <div className="md:hidden flex flex-col gap-4 p-4 pt-0">
          {data.todaysSchedule.map((wo) => (
              <div key={wo.id + '_mobile'} className="border border-surface-200 rounded-xl p-4 bg-white shadow-sm flex flex-col gap-3">
                <div className="flex justify-between items-start">
                  <div>
                    <h4 className="font-bold text-primary-600 cursor-pointer">WO-{wo.id}</h4>
                    <p className="text-sm font-medium text-surface-900">{wo.serviceType}</p>
                  </div>
                  <Badge variant={
                    wo.status === 'Completed' ? 'success' :
                    wo.status === 'In Progress' ? 'info' : 'default'
                  }>
                    {wo.status}
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
              </div>
            )
          )}
        </div>
      </Card>
        </>
      )}
    </div>
  );
}
