import React from 'react';
import { Filter, Search, Plus, MoreHorizontal } from 'lucide-react';
import { Card, Badge, Button } from '../components/ui';
import { SkeletonTable } from '../components/ui/Skeleton';
import { useApiList } from '../hooks/useApi';
import { WorkOrder } from '../types';

export function WorkOrders() {
  const { data: workOrders, loading } = useApiList<WorkOrder>('/work-orders');
  const list = workOrders ?? [];
  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 print:hidden">
        <div>
          <h1 className="text-2xl font-bold text-surface-900">Work Orders</h1>
          <p className="text-sm text-surface-500 mt-1">Manage and track all service requests.</p>
        </div>
        <div className="flex gap-2">
          <Button variant="outline" className="bg-white" onClick={() => window.print()}>
            Print View
          </Button>
          <Button>
            <Plus className="w-4 h-4 mr-2" />
            Create Work Order
          </Button>
        </div>
      </div>

      <Card>
        <div className="p-4 border-b border-surface-200 flex flex-col sm:flex-row gap-4 items-center justify-between bg-surface-50 rounded-t-xl">
          <div className="relative w-full sm:w-96">
            <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-surface-400" />
            <input 
              type="text" 
              placeholder="Search by ID, customer, or equipment..." 
              className="w-full pl-9 pr-4 py-2 border border-surface-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>
          <div className="flex gap-2 w-full sm:w-auto">
            <select className="border border-surface-200 rounded-lg text-sm px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
              <option>All Statuses</option>
              <option>Scheduled</option>
              <option>In Progress</option>
              <option>Completed</option>
            </select>
            <Button variant="outline" className="px-3">
              <Filter className="w-4 h-4" />
              More Filters
            </Button>
          </div>
        </div>

        {loading ? <SkeletonTable /> : (
        <div className="overflow-x-auto">
          <table className="w-full text-sm text-left">
            <thead className="text-xs text-surface-500 uppercase bg-surface-50 border-b border-surface-200">
              <tr>
                <th className="px-5 py-3 font-semibold">Work Order</th>
                <th className="px-5 py-3 font-semibold">Customer / Location</th>
                <th className="px-5 py-3 font-semibold">Service & Priority</th>
                <th className="px-5 py-3 font-semibold">Scheduled Date</th>
                <th className="px-5 py-3 font-semibold">Technician</th>
                <th className="px-5 py-3 font-semibold">Status</th>
                <th className="px-5 py-3 font-semibold"></th>
              </tr>
            </thead>
            <tbody className="divide-y divide-surface-100 bg-white">
              {list.map((wo) => (
                <tr key={wo.id} className="hover:bg-surface-50 transition-colors cursor-pointer group">
                  <td className="px-5 py-4 whitespace-nowrap">
                    <div className="font-semibold text-surface-900">WO-{wo.id}</div>
                    <div className="text-xs text-surface-500 mt-0.5 truncate max-w-[120px]" title={wo.equipmentName}>{wo.equipmentName}</div>
                  </td>
                  <td className="px-5 py-4">
                    <div className="font-medium text-surface-900">{wo.customerName}</div>
                    <div className="text-xs text-surface-500 mt-0.5">{wo.locationName}</div>
                  </td>
                  <td className="px-5 py-4">
                    <div className="font-medium text-surface-900">{wo.serviceType}</div>
                    <div className="mt-1">
                      <Badge variant={
                        wo.priority === 'Urgent' ? 'error' : 
                        wo.priority === 'High' ? 'warning' : 'default'
                      } className="text-[10px] px-1.5 py-0.5">
                        {wo.priority}
                      </Badge>
                    </div>
                  </td>
                  <td className="px-5 py-4 whitespace-nowrap">
                    <div className="font-medium text-surface-900">{wo.scheduledDate}</div>
                    <div className="text-xs text-surface-500 mt-0.5">{wo.scheduledTime}</div>
                  </td>
                  <td className="px-5 py-4 whitespace-nowrap">
                    {wo.technicianName ? (
                      <div className="flex items-center gap-2">
                        <div className="w-6 h-6 rounded-full bg-primary-100 flex items-center justify-center text-[10px] font-bold text-primary-700">
                          {wo.technicianName.split(' ').map(n => n[0]).join('')}
                        </div>
                        <span className="font-medium text-surface-900">{wo.technicianName}</span>
                      </div>
                    ) : (
                      <span className="text-surface-400 italic">Unassigned</span>
                    )}
                  </td>
                  <td className="px-5 py-4 whitespace-nowrap">
                    <Badge variant={
                      wo.status === 'Completed' ? 'success' : 
                      wo.status === 'In Progress' ? 'info' : 
                      wo.status === 'On Hold' ? 'warning' : 'default'
                    }>
                      {wo.status}
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
        <div className="p-4 border-t border-surface-200 flex items-center justify-between text-sm text-surface-500 bg-surface-50 rounded-b-xl">
          <span>Showing 1 to {list.length} of {list.length} entries</span>
          <div className="flex gap-1">
            <Button variant="outline" size="sm" disabled>Previous</Button>
            <Button variant="outline" size="sm" disabled>Next</Button>
          </div>
        </div>
      </Card>
    </div>
  );
}
