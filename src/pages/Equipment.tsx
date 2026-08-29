import React from 'react';
import { useNavigate } from 'react-router-dom';
import { Search, Filter, Plus, Wind, Activity, MoreHorizontal, AlertTriangle } from 'lucide-react';
import { Card, Badge, Button } from '../components/ui';
import { SkeletonTable } from '../components/ui/Skeleton';
import { useApiList } from '../hooks/useApi';
import { Equipment as EquipmentType } from '../types';

export function Equipment() {
  const navigate = useNavigate();
  const { data: equipmentList, loading } = useApiList<EquipmentType>('/equipment');
  const healthy = equipmentList?.filter(e => e.status === 'Good').length ?? 0;
  const needsAttention = equipmentList?.filter(e => e.status === 'Attention').length ?? 0;
  const critical = equipmentList?.filter(e => e.status === 'Critical').length ?? 0;

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 print:hidden">
        <div>
          <h1 className="text-2xl font-bold text-surface-900">HVAC Equipment</h1>
          <p className="text-sm text-surface-500 mt-1">Track assets, warranties, and unit health.</p>
        </div>
        <div className="flex gap-2">
          <Button variant="outline" className="bg-white" onClick={() => window.print()}>
            Print View
          </Button>
          <Button>
            <Plus className="w-4 h-4 mr-2" />
            Add Equipment
          </Button>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <Card className="p-4 border-l-4 border-success-500">
          <div className="flex items-center gap-4">
            <div className="w-10 h-10 bg-success-50 rounded-lg flex items-center justify-center text-success-600">
              <Activity className="w-5 h-5" />
            </div>
            <div>
              <p className="text-sm font-medium text-surface-500">Healthy Units</p>
              <h3 className="text-2xl font-bold text-surface-900">{healthy}</h3>
            </div>
          </div>
        </Card>
        <Card className="p-4 border-l-4 border-warning-500">
          <div className="flex items-center gap-4">
            <div className="w-10 h-10 bg-warning-50 rounded-lg flex items-center justify-center text-warning-600">
              <AlertTriangle className="w-5 h-5" />
            </div>
            <div>
              <p className="text-sm font-medium text-surface-500">Requires Attention</p>
              <h3 className="text-2xl font-bold text-surface-900">{needsAttention}</h3>
            </div>
          </div>
        </Card>
        <Card className="p-4 border-l-4 border-error-500">
          <div className="flex items-center gap-4">
            <div className="w-10 h-10 bg-error-50 rounded-lg flex items-center justify-center text-error-600">
              <Wind className="w-5 h-5" />
            </div>
            <div>
              <p className="text-sm font-medium text-surface-500">Critical / Offline</p>
              <h3 className="text-2xl font-bold text-surface-900">{critical}</h3>
            </div>
          </div>
        </Card>
      </div>

      <Card>
        <div className="p-4 border-b border-surface-200 flex flex-col sm:flex-row gap-4 items-center justify-between bg-surface-50 rounded-t-xl">
          <div className="relative w-full sm:w-96">
            <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-surface-400" />
            <input 
              type="text" 
              placeholder="Search by serial number, brand, or model..." 
              className="w-full pl-9 pr-4 py-2 border border-surface-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white"
            />
          </div>
          <div className="flex gap-2 w-full sm:w-auto">
            <select className="border border-surface-200 rounded-lg text-sm px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
              <option>All Types</option>
              <option>VRV System</option>
              <option>Rooftop Unit</option>
              <option>Chiller</option>
            </select>
            <Button variant="outline" className="px-3">
              <Filter className="w-4 h-4" />
            </Button>
          </div>
        </div>

        {loading ? <SkeletonTable /> : (
        <div className="overflow-x-auto">
          <table className="w-full text-sm text-left">
            <thead className="text-xs text-surface-500 uppercase bg-surface-50 border-b border-surface-200">
              <tr>
                <th className="px-5 py-3 font-semibold">Equipment / ID</th>
                <th className="px-5 py-3 font-semibold">Brand & Model</th>
                <th className="px-5 py-3 font-semibold">Location</th>
                <th className="px-5 py-3 font-semibold">Install & Warranty</th>
                <th className="px-5 py-3 font-semibold">Health Status</th>
                <th className="px-5 py-3 font-semibold"></th>
              </tr>
            </thead>
            <tbody className="divide-y divide-surface-100 bg-white">
              {(equipmentList ?? []).map((eq) => (
                <tr 
                  key={eq.id} 
                  onClick={() => navigate(`/equipment/${eq.id}`)}
                  className="hover:bg-surface-50 transition-colors cursor-pointer group"
                >
                  <td className="px-5 py-4 whitespace-nowrap">
                    <div className="font-semibold text-surface-900">{eq.type}</div>
                    <div className="text-xs font-mono text-surface-500 mt-0.5">{eq.id}</div>
                  </td>
                  <td className="px-5 py-4 whitespace-nowrap">
                    <div className="font-medium text-surface-900">{eq.brand}</div>
                    <div className="text-xs text-surface-500 mt-0.5">SN: {eq.serialNumber}</div>
                  </td>
                  <td className="px-5 py-4">
                    <div className="font-medium text-surface-900">{eq.locationName}</div>
                  </td>
                  <td className="px-5 py-4 whitespace-nowrap">
                    <div className="text-surface-900">{eq.installationDate}</div>
                    <div className="text-xs text-surface-500 mt-0.5">Exp: {eq.warrantyExpiration}</div>
                  </td>
                  <td className="px-5 py-4 whitespace-nowrap">
                    <Badge variant={
                      eq.status === 'Good' ? 'success' : 
                      eq.status === 'Critical' ? 'error' : 'warning'
                    }>
                      {eq.status}
                    </Badge>
                  </td>
                  <td className="px-5 py-4 whitespace-nowrap text-right">
                    <button className="text-surface-400 hover:text-surface-900 p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                      <MoreHorizontal className="w-5 h-5" />
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
        )}
      </Card>
    </div>
  );
}
