import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, Wind, Activity, Wrench, AlertTriangle, CheckCircle, Clock as ClockIcon } from 'lucide-react';
import { Card, Badge, Button } from '../../components/ui';
import { Skeleton, SkeletonCard } from '../../components/ui/Skeleton';
import { ActivityFeed } from '../../components/ActivityFeed';
import { mockEquipment, mockWorkOrders, mockActivities } from '../../data/mockData';

export function EquipmentProfile() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const timer = setTimeout(() => setLoading(false), 500);
    return () => clearTimeout(timer);
  }, [id]);
  
  const equipment = mockEquipment.find(e => e.id === id) || mockEquipment[0];
  const relatedOrders = mockWorkOrders.filter(wo => wo.equipmentId === equipment.id);
  const equipmentActivities = mockActivities.filter(a => a.relatedId === equipment.id);

  // Mock timeline events
  const timeline = [
    { date: '2026-08-28', event: 'Scheduled Maintenance', tech: 'Carlos Martinez', status: 'Upcoming' },
    { date: '2026-05-10', event: 'Filter Replacement & Coil Clean', tech: 'Marcus Johnson', status: 'Completed' },
    { date: '2025-11-22', event: 'Annual Inspection', tech: 'Sarah O\'Connor', status: 'Completed' },
    { date: equipment.installationDate, event: 'System Installation', tech: 'David Kim', status: 'Completed' },
  ];

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
            <SkeletonCard />
            <SkeletonCard />
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center gap-4">
        <Button variant="ghost" size="icon" onClick={() => navigate('/equipment')} className="text-surface-500 hover:text-surface-900">
          <ArrowLeft className="w-5 h-5" />
        </Button>
        <div>
          <h1 className="text-2xl font-bold text-surface-900">Equipment Details</h1>
          <p className="text-sm text-surface-500 mt-1">Asset specifications and service history.</p>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Left Column: Specs & Health */}
        <div className="space-y-6">
          <Card className="p-6">
            <div className="flex items-start justify-between mb-6">
              <div className="flex items-center gap-4">
                <div className="w-12 h-12 rounded-xl bg-surface-100 flex items-center justify-center text-surface-600">
                  <Wind className="w-6 h-6" />
                </div>
                <div>
                  <h2 className="text-xl font-bold text-surface-900">{equipment.brand}</h2>
                  <p className="text-sm font-medium text-surface-500">{equipment.model}</p>
                </div>
              </div>
              <Badge variant={
                equipment.status === 'Good' ? 'success' : 
                equipment.status === 'Critical' ? 'error' : 'warning'
              } className="text-sm px-3 py-1">
                {equipment.status}
              </Badge>
            </div>

            <div className="space-y-4">
              <div className="grid grid-cols-2 gap-4 pb-4 border-b border-surface-100">
                <div>
                  <p className="text-xs text-surface-500 mb-1">Asset ID</p>
                  <p className="font-mono text-sm font-semibold text-surface-900">{equipment.id}</p>
                </div>
                <div>
                  <p className="text-xs text-surface-500 mb-1">Type</p>
                  <p className="font-medium text-sm text-surface-900">{equipment.type}</p>
                </div>
              </div>
              
              <div className="grid grid-cols-2 gap-4 pb-4 border-b border-surface-100">
                <div>
                  <p className="text-xs text-surface-500 mb-1">Serial Number</p>
                  <p className="font-mono text-sm font-medium text-surface-900">{equipment.serialNumber}</p>
                </div>
                <div>
                  <p className="text-xs text-surface-500 mb-1">Location</p>
                  <p className="font-medium text-sm text-surface-900">{equipment.locationName}</p>
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <p className="text-xs text-surface-500 mb-1">Installation Date</p>
                  <p className="font-medium text-sm text-surface-900">{equipment.installationDate}</p>
                </div>
                <div>
                  <p className="text-xs text-surface-500 mb-1">Warranty Expiration</p>
                  <p className="font-medium text-sm text-surface-900">{equipment.warrantyExpiration}</p>
                </div>
              </div>
            </div>
          </Card>

          <Card className="p-6 bg-surface-900 text-white">
            <h3 className="font-bold text-white mb-4 flex items-center gap-2">
              <Activity className="w-5 h-5 text-primary-400" /> Equipment Health
            </h3>
            {equipment.status === 'Good' ? (
              <div className="text-center py-4">
                <CheckCircle className="w-12 h-12 text-success-500 mx-auto mb-3" />
                <h4 className="text-lg font-bold">Operating Normally</h4>
                <p className="text-sm text-surface-400 mt-1">All telemetry data within expected parameters.</p>
              </div>
            ) : equipment.status === 'Attention' ? (
              <div className="text-center py-4">
                <AlertTriangle className="w-12 h-12 text-warning-500 mx-auto mb-3" />
                <h4 className="text-lg font-bold">Maintenance Advised</h4>
                <p className="text-sm text-surface-400 mt-1">Minor anomalies detected. Service recommended.</p>
              </div>
            ) : (
              <div className="text-center py-4">
                <AlertTriangle className="w-12 h-12 text-error-500 mx-auto mb-3" />
                <h4 className="text-lg font-bold">Critical Issue</h4>
                <p className="text-sm text-surface-400 mt-1">System offline or experiencing severe faults.</p>
              </div>
            )}
          </Card>
        </div>

        {/* Right Column: Service Timeline & Work Orders */}
        <div className="lg:col-span-2 space-y-6">
          <Card className="p-6">
            <h3 className="font-bold text-surface-900 mb-6 flex items-center gap-2">
              <ClockIcon className="w-5 h-5 text-surface-400" /> Service Timeline
            </h3>
            
            <div className="relative pl-6 space-y-8 before:absolute before:inset-0 before:ml-[11px] before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-surface-200">
              {timeline.map((item, idx) => (
                <div key={idx} className="relative flex items-start gap-4">
                  <div className={`absolute left-0 -translate-x-[25px] w-4 h-4 rounded-full border-4 border-white ${item.status === 'Upcoming' ? 'bg-primary-500' : 'bg-surface-300'}`}></div>
                  <div className="flex-1">
                    <div className="flex items-center gap-3 mb-1">
                      <span className="font-semibold text-surface-900">{item.event}</span>
                      <Badge variant={item.status === 'Upcoming' ? 'info' : 'default'} className="text-[10px] px-2 py-0.5">{item.status}</Badge>
                    </div>
                    <div className="text-sm text-surface-500">
                      <span>{item.date}</span> • <span>Tech: {item.tech}</span>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </Card>

          <Card className="p-6">
            <div className="flex items-center justify-between mb-4">
              <h3 className="font-bold text-surface-900 flex items-center gap-2">
                <Wrench className="w-5 h-5 text-surface-400" /> Active Work Orders
              </h3>
              <Button size="sm" variant="outline">Create WO</Button>
            </div>
            
            {relatedOrders.length > 0 ? (
              <div className="divide-y divide-surface-100 border border-surface-200 rounded-lg overflow-hidden">
                {relatedOrders.map(wo => (
                  <div key={wo.id} className="p-4 flex items-center justify-between bg-white hover:bg-surface-50 transition-colors">
                    <div>
                      <h4 className="font-semibold text-surface-900 text-sm">{wo.serviceType} <span className="text-surface-400 font-normal">#{wo.id}</span></h4>
                      <p className="text-xs text-surface-500 mt-1">{wo.description}</p>
                    </div>
                    <div className="text-right">
                      <Badge variant={wo.status === 'Scheduled' ? 'info' : wo.status === 'In Progress' ? 'warning' : 'default'} className="mb-1">
                        {wo.status}
                      </Badge>
                      <p className="text-xs font-medium text-surface-900">{wo.scheduledDate}</p>
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <div className="p-8 text-center bg-surface-50 rounded-lg border border-surface-200 border-dashed">
                <p className="text-surface-500 text-sm">No active work orders for this equipment.</p>
              </div>
            )}
          </Card>

          <ActivityFeed activities={equipmentActivities} title="Equipment Activity Log" />
        </div>
      </div>
    </div>
  );
}
