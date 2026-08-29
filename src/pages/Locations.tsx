import React from 'react';
import { Search, MapPin, Plus, MoreHorizontal } from 'lucide-react';
import { Card, Button } from '../components/ui';
import { SkeletonTable } from '../components/ui/Skeleton';
import { useApiList } from '../hooks/useApi';
import { Location } from '../types';

export function Locations() {
  const { data: locations, loading } = useApiList<Location>('/locations');

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 className="text-2xl font-bold text-surface-900">Locations</h1>
          <p className="text-sm text-surface-500 mt-1">Manage physical service sites and properties.</p>
        </div>
        <Button>
          <Plus className="w-4 h-4" />
          Add Location
        </Button>
      </div>

      <Card>
        <div className="p-4 border-b border-surface-200 flex flex-col sm:flex-row gap-4 items-center justify-between bg-surface-50 rounded-t-xl">
          <div className="relative w-full sm:w-96">
            <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-surface-400" />
            <input 
              type="text" 
              placeholder="Search by address or location name..." 
              className="w-full pl-9 pr-4 py-2 border border-surface-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white"
            />
          </div>
        </div>

        {loading ? <SkeletonTable /> : (
        <div className="overflow-x-auto">
          <table className="w-full text-sm text-left">
            <thead className="text-xs text-surface-500 uppercase bg-surface-50 border-b border-surface-200">
              <tr>
                <th className="px-5 py-3 font-semibold">Location / Address</th>
                <th className="px-5 py-3 font-semibold">Customer</th>
                <th className="px-5 py-3 font-semibold">Site Contact</th>
                <th className="px-5 py-3 font-semibold text-center">Equipment</th>
                <th className="px-5 py-3 font-semibold">Last Visit</th>
                <th className="px-5 py-3 font-semibold"></th>
              </tr>
            </thead>
            <tbody className="divide-y divide-surface-100 bg-white">
              {(locations ?? []).map((loc) => (
                <tr key={loc.id} className="hover:bg-surface-50 transition-colors cursor-pointer group">
                  <td className="px-5 py-4">
                    <div className="font-semibold text-surface-900 flex items-center gap-2">
                      <MapPin className="w-3.5 h-3.5 text-surface-400" />
                      {loc.name}
                    </div>
                    <div className="text-xs text-surface-500 mt-0.5 ml-5.5">{loc.address}</div>
                  </td>
                  <td className="px-5 py-4 whitespace-nowrap">
                    <span className="text-primary-600 font-medium hover:underline">{loc.customerName}</span>
                  </td>
                  <td className="px-5 py-4 whitespace-nowrap">
                    <div className="font-medium text-surface-900">{loc.contactName}</div>
                    <div className="text-xs text-surface-500 mt-0.5">{loc.contactPhone}</div>
                  </td>
                  <td className="px-5 py-4 text-center font-medium text-surface-900">{loc.equipmentCount}</td>
                  <td className="px-5 py-4 whitespace-nowrap">
                    <div className="font-medium text-surface-900">{loc.lastVisit}</div>
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
