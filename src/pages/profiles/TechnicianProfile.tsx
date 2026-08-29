import React from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, Star, Clock, MapPin, CheckCircle, Wrench } from 'lucide-react';
import { Card, Badge, Button } from '../../components/ui';
import { Skeleton, SkeletonCard } from '../../components/ui/Skeleton';
import { ActivityFeed } from '../../components/ActivityFeed';
import { useApiResource, useApiList } from '../../hooks/useApi';
import { Technician, WorkOrder } from '../../types';
import { BarChart, Bar, XAxis, YAxis, Tooltip, ResponsiveContainer, CartesianGrid } from 'recharts';

const WEEKDAYS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

export function TechnicianProfile() {
  const { id } = useParams();
  const navigate = useNavigate();

  const { data: tech, loading } = useApiResource<Technician>(id ? `/technicians/${id}` : null);
  const { data: techOrders } = useApiList<WorkOrder>(id ? `/work-orders?technician_id=${id}` : null);

  const orders = techOrders ?? [];
  const currentJob = orders.find(wo => wo.id === tech?.currentJobId);

  const performanceData = WEEKDAYS.map(day => {
    const dayOrders = orders.filter(wo => {
      const d = new Date(wo.scheduledDate + 'T00:00:00');
      return WEEKDAYS[(d.getDay() + 6) % 7] === day;
    });
    return {
      name: day,
      hours: dayOrders.reduce((sum, wo) => sum + wo.durationHours, 0),
      jobs: dayOrders.filter(wo => wo.status === 'Completed').length,
    };
  });

  if (loading || !tech) {
    return (
      <div className="space-y-6">
        <div className="flex items-center gap-4">
          <Skeleton className="w-10 h-10 rounded-lg" />
          <div>
            <Skeleton className="w-48 h-8 mb-2" />
            <Skeleton className="w-64 h-4" />
          </div>
        </div>
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div className="space-y-6">
            <SkeletonCard />
            <SkeletonCard />
          </div>
          <div className="lg:col-span-2 space-y-6">
            <div className="grid grid-cols-3 gap-4">
              <SkeletonCard /><SkeletonCard /><SkeletonCard />
            </div>
            <Skeleton className="h-72 w-full rounded-xl" />
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center gap-4">
        <Button variant="ghost" size="icon" onClick={() => navigate('/technicians')} className="text-surface-500 hover:text-surface-900">
          <ArrowLeft className="w-5 h-5" />
        </Button>
        <div>
          <h1 className="text-2xl font-bold text-surface-900">Technician Profile</h1>
          <p className="text-sm text-surface-500 mt-1">Performance metrics and current assignments.</p>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Left Column: Profile Card */}
        <div className="space-y-6">
          <Card className="p-6 flex flex-col items-center text-center relative overflow-hidden">
            <div className="absolute top-0 left-0 w-full h-24 bg-gradient-to-br from-primary-600 to-primary-800"></div>

            <div className="relative z-10 w-24 h-24 rounded-full bg-white p-1 shadow-md mb-4 mt-8">
              <div className="w-full h-full rounded-full bg-primary-100 flex items-center justify-center text-primary-700 text-3xl font-bold">
                {tech.avatar}
              </div>
            </div>

            <h2 className="text-xl font-bold text-surface-900">{tech.name}</h2>
            <p className="text-sm text-surface-500 mb-4">{tech.email}</p>

            <Badge variant={
              tech.status === 'Available' ? 'success' :
              tech.status === 'On Site' ? 'warning' :
              tech.status === 'In Transit' ? 'info' : 'default'
            } className="mb-6">
              {tech.status}
            </Badge>

            <div className="w-full text-left">
              <h3 className="text-xs font-bold text-surface-400 uppercase tracking-wider mb-3">Core Skills</h3>
              <div className="flex flex-wrap gap-2 justify-center">
                {tech.skills.map(skill => (
                  <span key={skill} className="px-2.5 py-1 bg-surface-100 text-surface-700 text-sm rounded-md font-medium border border-surface-200">
                    {skill}
                  </span>
                ))}
              </div>
            </div>
          </Card>

          <Card className="p-5">
            <h3 className="font-bold text-surface-900 mb-4 flex items-center gap-2">
              <Wrench className="w-4 h-4 text-surface-400" /> Current Assignment
            </h3>
            {currentJob ? (
              <div className="space-y-3">
                <div>
                  <p className="text-xs text-surface-500">Customer</p>
                  <p className="font-semibold text-surface-900">{currentJob.customerName}</p>
                </div>
                <div>
                  <p className="text-xs text-surface-500">Location</p>
                  <p className="font-medium text-surface-800 flex items-center gap-1">
                    <MapPin className="w-3.5 h-3.5 text-primary-500" />
                    {currentJob.locationName}
                  </p>
                </div>
                <div className="pt-3 border-t border-surface-100">
                  <p className="text-sm text-surface-700">{currentJob.description}</p>
                </div>
                <Button className="w-full mt-2" variant="outline" onClick={() => navigate('/work-orders')}>View Work Order</Button>
              </div>
            ) : (
              <div className="text-center py-6 text-surface-500">
                <CheckCircle className="w-8 h-8 text-surface-300 mx-auto mb-2" />
                <p>No active assignments</p>
              </div>
            )}
          </Card>
        </div>

        {/* Right Column: Metrics & Charts */}
        <div className="lg:col-span-2 space-y-6">
          <div className="grid grid-cols-3 gap-4">
            <Card className="p-4 flex flex-col items-center justify-center text-center">
              <Star className="w-6 h-6 text-warning-500 fill-warning-500 mb-2" />
              <p className="text-sm text-surface-500">Avg Rating</p>
              <h3 className="text-2xl font-bold text-surface-900">{tech.rating}</h3>
            </Card>
            <Card className="p-4 flex flex-col items-center justify-center text-center">
              <CheckCircle className="w-6 h-6 text-success-500 mb-2" />
              <p className="text-sm text-surface-500">Completion Rate</p>
              <h3 className="text-2xl font-bold text-surface-900">{tech.completionRate}%</h3>
            </Card>
            <Card className="p-4 flex flex-col items-center justify-center text-center">
              <Clock className="w-6 h-6 text-primary-500 mb-2" />
              <p className="text-sm text-surface-500">Hours This Week</p>
              <h3 className="text-2xl font-bold text-surface-900">{tech.hoursThisWeek}</h3>
            </Card>
          </div>

          <Card className="p-6">
            <h3 className="font-bold text-surface-900 mb-6">Scheduled Hours vs. Jobs Completed (This Week)</h3>
            <div className="h-72 w-full">
              <ResponsiveContainer width="100%" height="100%">
                <BarChart data={performanceData} margin={{ top: 10, right: 10, left: -20, bottom: 0 }}>
                  <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#E5E7EB" />
                  <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{ fontSize: 12, fill: '#6B7280' }} />
                  <YAxis yAxisId="left" orientation="left" axisLine={false} tickLine={false} tick={{ fontSize: 12, fill: '#6B7280' }} />
                  <YAxis yAxisId="right" orientation="right" axisLine={false} tickLine={false} tick={{ fontSize: 12, fill: '#6B7280' }} />
                  <Tooltip
                    cursor={{ fill: '#F3F4F6' }}
                    contentStyle={{ borderRadius: '8px', border: 'none', boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)' }}
                  />
                  <Bar yAxisId="left" dataKey="hours" name="Hours Scheduled" fill="#0369a1" radius={[4, 4, 0, 0]} barSize={32} />
                  <Bar yAxisId="right" dataKey="jobs" name="Jobs Completed" fill="#38bdf8" radius={[4, 4, 0, 0]} barSize={32} />
                </BarChart>
              </ResponsiveContainer>
            </div>
          </Card>

          {/* Real activity feed isn't wired up yet (no Activities API); shown empty rather than stale mock data. */}
          <ActivityFeed activities={[]} title="Technician Activity Log" />
        </div>
      </div>
    </div>
  );
}
