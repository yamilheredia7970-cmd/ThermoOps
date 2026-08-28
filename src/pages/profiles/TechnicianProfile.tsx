import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, Star, Clock, MapPin, CheckCircle, AlertCircle, Wrench } from 'lucide-react';
import { Card, Badge, Button } from '../../components/ui';
import { Skeleton, SkeletonCard } from '../../components/ui/Skeleton';
import { ActivityFeed } from '../../components/ActivityFeed';
import { mockTechnicians, mockWorkOrders, mockActivities } from '../../data/mockData';
import { BarChart, Bar, XAxis, YAxis, Tooltip, ResponsiveContainer, CartesianGrid } from 'recharts';

const performanceData = [
  { name: 'Mon', hours: 8, jobs: 3 },
  { name: 'Tue', hours: 7.5, jobs: 4 },
  { name: 'Wed', hours: 9, jobs: 3 },
  { name: 'Thu', hours: 8, jobs: 5 },
  { name: 'Fri', hours: 0, jobs: 0 },
];

export function TechnicianProfile() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const timer = setTimeout(() => setLoading(false), 500);
    return () => clearTimeout(timer);
  }, [id]);
  
  const tech = mockTechnicians.find(t => t.id === id) || mockTechnicians[0];
  const techOrders = mockWorkOrders.filter(wo => wo.technicianId === tech.id);
  const currentJob = techOrders.find(wo => wo.id === tech.currentJobId);
  const techActivities = mockActivities.filter(a => a.actor === tech.name);

  if (loading) {
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
                <Button className="w-full mt-2" variant="outline">View Work Order</Button>
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
            <h3 className="font-bold text-surface-900 mb-6">Weekly Hours vs. Jobs Completed</h3>
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
                  <Bar yAxisId="left" dataKey="hours" name="Hours Worked" fill="#0369a1" radius={[4, 4, 0, 0]} barSize={32} />
                  <Bar yAxisId="right" dataKey="jobs" name="Jobs Completed" fill="#38bdf8" radius={[4, 4, 0, 0]} barSize={32} />
                </BarChart>
              </ResponsiveContainer>
            </div>
          </Card>
          
          <ActivityFeed activities={techActivities} title="Technician Activity Log" />
        </div>
      </div>
    </div>
  );
}
