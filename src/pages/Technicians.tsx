import React from 'react';
import { useNavigate } from 'react-router-dom';
import { Search, Plus, MapPin, Star, Clock } from 'lucide-react';
import { Card, Badge, Button } from '../components/ui';
import { SkeletonCard } from '../components/ui/Skeleton';
import { useApiList } from '../hooks/useApi';
import { Technician } from '../types';

export function Technicians() {
  const navigate = useNavigate();
  const { data: technicians, loading } = useApiList<Technician>('/technicians');
  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 print:hidden">
        <div>
          <h1 className="text-2xl font-bold text-surface-900">Technicians</h1>
          <p className="text-sm text-surface-500 mt-1">Manage field team, skills, and performance.</p>
        </div>
        <div className="flex gap-2">
          <Button variant="outline" className="bg-white" onClick={() => window.print()}>
            Print View
          </Button>
          <Button>
            <Plus className="w-4 h-4 mr-2" />
            Add Technician
          </Button>
        </div>
      </div>

      <div className="flex gap-4 items-center bg-white p-2 rounded-xl border border-surface-200">
        <div className="relative flex-1 max-w-md">
          <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-surface-400" />
          <input 
            type="text" 
            placeholder="Search technicians by name or skill..." 
            className="w-full pl-9 pr-4 py-2 border-none rounded-lg text-sm focus:outline-none focus:ring-0 bg-transparent"
          />
        </div>
        <div className="h-6 w-px bg-surface-200"></div>
        <div className="flex gap-2 px-2 overflow-x-auto no-scrollbar">
          <Badge variant="default" className="cursor-pointer bg-surface-800 text-white hover:bg-surface-700">All</Badge>
          <Badge variant="default" className="cursor-pointer hover:bg-surface-200">Commercial</Badge>
          <Badge variant="default" className="cursor-pointer hover:bg-surface-200">Residential</Badge>
          <Badge variant="default" className="cursor-pointer hover:bg-surface-200">Chillers</Badge>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        {loading && Array.from({ length: 3 }).map((_, i) => <SkeletonCard key={i} />)}
        {(technicians ?? []).map((tech) => (
          <Card 
            key={tech.id} 
            onClick={() => navigate(`/technicians/${tech.id}`)}
            className="group hover:border-primary-300 transition-colors cursor-pointer flex flex-col"
          >
            <div className="p-5 flex-1">
              <div className="flex justify-between items-start mb-4">
                <div className="flex items-center gap-4">
                  <div className="w-14 h-14 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-bold text-xl shadow-sm">
                    {tech.avatar}
                  </div>
                  <div>
                    <h3 className="font-bold text-surface-900 text-lg">{tech.name}</h3>
                    <div className="flex items-center gap-1.5 mt-1 text-sm text-surface-500">
                      <span className={`w-2 h-2 rounded-full ${
                        tech.status === 'Available' ? 'bg-success-500' : 
                        tech.status === 'On Site' ? 'bg-warning-500' : 
                        tech.status === 'In Transit' ? 'bg-accent-500' : 'bg-surface-400'
                      }`}></span>
                      {tech.status}
                    </div>
                  </div>
                </div>
              </div>
              
              <div className="space-y-4">
                <div>
                  <p className="text-xs font-semibold text-surface-400 uppercase tracking-wider mb-2">Skills</p>
                  <div className="flex flex-wrap gap-2">
                    {tech.skills.map(skill => (
                      <span key={skill} className="px-2 py-1 bg-surface-100 text-surface-700 text-xs rounded-md font-medium">
                        {skill}
                      </span>
                    ))}
                  </div>
                </div>

                <div className="grid grid-cols-3 gap-2 py-3 border-t border-surface-100">
                  <div>
                    <p className="text-xs text-surface-500 mb-0.5">Rating</p>
                    <p className="text-sm font-bold text-surface-900 flex items-center gap-1">
                      {tech.rating} <Star className="w-3.5 h-3.5 text-warning-500 fill-warning-500" />
                    </p>
                  </div>
                  <div>
                    <p className="text-xs text-surface-500 mb-0.5">Jobs Today</p>
                    <p className="text-sm font-bold text-surface-900">{tech.jobsToday}</p>
                  </div>
                  <div>
                    <p className="text-xs text-surface-500 mb-0.5">Weekly Hrs</p>
                    <p className="text-sm font-bold text-surface-900 flex items-center gap-1">
                      {tech.hoursThisWeek} <Clock className="w-3.5 h-3.5 text-surface-400" />
                    </p>
                  </div>
                </div>
              </div>
            </div>
            
            {tech.currentJobId ? (
              <div className="bg-primary-50 px-5 py-3 border-t border-primary-100 flex items-center justify-between">
                <div className="flex items-center gap-2 text-sm">
                  <MapPin className="w-4 h-4 text-primary-600" />
                  <span className="font-medium text-primary-900">Active Job: <span className="underline">{tech.currentJobId}</span></span>
                </div>
                <Button size="sm" variant="outline" className="bg-white hover:bg-white border-primary-200 text-primary-700">View</Button>
              </div>
            ) : (
              <div className="bg-surface-50 px-5 py-3 border-t border-surface-100 flex items-center justify-between">
                <span className="text-sm text-surface-500 font-medium">No active job</span>
                <Button size="sm" variant="secondary">Assign</Button>
              </div>
            )}
          </Card>
        ))}
      </div>
    </div>
  );
}
