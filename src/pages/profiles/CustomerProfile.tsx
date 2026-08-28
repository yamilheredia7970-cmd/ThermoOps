import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, MapPin, Wind, Building2 } from 'lucide-react';
import { Card, Badge, Button } from '../../components/ui';
import { Skeleton, SkeletonCard, SkeletonTable } from '../../components/ui/Skeleton';
import { ActivityFeed } from '../../components/ActivityFeed';
import { mockCustomers, mockLocations, mockEquipment, mockWorkOrders, mockActivities } from '../../data/mockData';

export function CustomerProfile() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [activeTab, setActiveTab] = useState<'locations' | 'equipment' | 'workorders'>('locations');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const timer = setTimeout(() => setLoading(false), 500);
    return () => clearTimeout(timer);
  }, [id]);

  const customer = mockCustomers.find(c => c.id === id) || mockCustomers[0];
  const customerLocations = mockLocations.filter(l => l.customerId === customer.id);
  const customerEquipment = mockEquipment.filter(e => e.customerId === customer.id);
  const customerOrders = mockWorkOrders.filter(wo => wo.customerId === customer.id);
  const customerActivities = mockActivities.filter(a => a.relatedId === customer.id);

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
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
          <SkeletonCard /><SkeletonCard /><SkeletonCard /><SkeletonCard />
        </div>
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div className="lg:col-span-2"><SkeletonTable rows={4} /></div>
          <Skeleton className="h-[400px] rounded-xl" />
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center gap-4">
        <Button variant="ghost" size="icon" onClick={() => navigate('/customers')} className="text-surface-500 hover:text-surface-900">
          <ArrowLeft className="w-5 h-5" />
        </Button>
        <div>
          <h1 className="text-2xl font-bold text-surface-900">{customer.name}</h1>
          <div className="flex items-center gap-3 text-sm text-surface-500 mt-1">
            <span className="flex items-center gap-1"><Building2 className="w-4 h-4" /> {customer.type}</span>
            <span>•</span>
            <span>Customer since {new Date(customer.since).getFullYear()}</span>
            <span>•</span>
            <Badge variant={customer.status === 'Active' ? 'success' : 'default'}>{customer.status}</Badge>
          </div>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
        <Card className="p-5 flex flex-col gap-1 border-l-4 border-l-primary-500">
          <span className="text-sm font-medium text-surface-500">Total Locations</span>
          <span className="text-3xl font-bold text-surface-900">{customer.locationsCount}</span>
        </Card>
        <Card className="p-5 flex flex-col gap-1 border-l-4 border-l-accent-500">
          <span className="text-sm font-medium text-surface-500">Total Equipment</span>
          <span className="text-3xl font-bold text-surface-900">{customer.equipmentCount}</span>
        </Card>
        <Card className="p-5 flex flex-col gap-1 border-l-4 border-l-warning-500">
          <span className="text-sm font-medium text-surface-500">Active Work Orders</span>
          <span className="text-3xl font-bold text-surface-900">{customer.activeWorkOrders}</span>
        </Card>
        <Card className="p-5 flex flex-col gap-1 border-l-4 border-l-success-500">
          <span className="text-sm font-medium text-surface-500">YTD Revenue</span>
          <span className="text-3xl font-bold text-surface-900">$24,500</span>
        </Card>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <Card className="lg:col-span-2 overflow-hidden flex flex-col">
          <div className="flex border-b border-surface-200 bg-surface-50 px-4 pt-4 gap-6">
            {(['locations', 'equipment', 'workorders'] as const).map(tab => (
              <button
                key={tab}
                onClick={() => setActiveTab(tab)}
                className={`pb-3 text-sm font-semibold capitalize border-b-2 transition-colors ${
                  activeTab === tab 
                    ? 'border-primary-600 text-primary-700' 
                    : 'border-transparent text-surface-500 hover:text-surface-700'
                }`}
              >
                {tab === 'workorders' ? 'Active Work Orders' : tab}
              </button>
            ))}
          </div>
          
          <div className="p-0 flex-1 overflow-auto">
            {activeTab === 'locations' && (
              <div className="divide-y divide-surface-100">
                {customerLocations.map(loc => (
                  <div key={loc.id} className="p-4 flex items-center justify-between hover:bg-surface-50">
                    <div className="flex items-center gap-3">
                      <div className="w-10 h-10 rounded-lg bg-surface-100 flex items-center justify-center text-surface-500">
                        <MapPin className="w-5 h-5" />
                      </div>
                      <div>
                        <h4 className="font-semibold text-surface-900">{loc.name}</h4>
                        <p className="text-sm text-surface-500">{loc.address}</p>
                      </div>
                    </div>
                    <div className="text-right">
                      <p className="text-sm font-medium text-surface-900">{loc.contactName}</p>
                      <p className="text-xs text-surface-500">{loc.contactPhone}</p>
                    </div>
                  </div>
                ))}
              </div>
            )}

            {activeTab === 'equipment' && (
              <div className="divide-y divide-surface-100">
                {customerEquipment.map(eq => (
                  <div key={eq.id} className="p-4 flex items-center justify-between hover:bg-surface-50 cursor-pointer" onClick={() => navigate(`/equipment/${eq.id}`)}>
                    <div className="flex items-center gap-3">
                      <div className="w-10 h-10 rounded-lg bg-surface-100 flex items-center justify-center text-surface-500">
                        <Wind className="w-5 h-5" />
                      </div>
                      <div>
                        <h4 className="font-semibold text-surface-900">{eq.type} - {eq.brand}</h4>
                        <p className="text-sm text-surface-500">SN: {eq.serialNumber} | Loc: {eq.locationName}</p>
                      </div>
                    </div>
                    <Badge variant={eq.status === 'Good' ? 'success' : eq.status === 'Critical' ? 'error' : 'warning'}>{eq.status}</Badge>
                  </div>
                ))}
              </div>
            )}

            {activeTab === 'workorders' && (
              <div className="divide-y divide-surface-100">
                {customerOrders.length > 0 ? customerOrders.map(wo => (
                  <div key={wo.id} className="p-4 flex items-center justify-between hover:bg-surface-50">
                    <div>
                      <h4 className="font-semibold text-surface-900">{wo.serviceType}</h4>
                      <p className="text-sm text-surface-500">{wo.description}</p>
                    </div>
                    <div className="flex items-center gap-4">
                      <div className="text-right">
                        <p className="text-sm font-medium text-surface-900">{wo.scheduledDate}</p>
                        <p className="text-xs text-surface-500">{wo.scheduledTime} ({wo.durationHours}h)</p>
                      </div>
                      <Badge variant={wo.status === 'Completed' ? 'success' : 'warning'}>{wo.status}</Badge>
                    </div>
                  </div>
                )) : (
                  <div className="p-8 text-center text-surface-500">No active work orders.</div>
                )}
              </div>
            )}
          </div>
        </Card>

        <ActivityFeed activities={customerActivities} className="h-full" />
      </div>
    </div>
  );
}
