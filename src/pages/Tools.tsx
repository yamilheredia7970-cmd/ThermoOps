import React from 'react';
import { Search, Plus, PenTool } from 'lucide-react';
import { Card, Badge, Button } from '../components/ui';
import { SkeletonTable } from '../components/ui/Skeleton';
import { useApiList } from '../hooks/useApi';
import { Tool } from '../types';

export function Tools() {
  const { data: tools, loading } = useApiList<Tool>('/tools');
  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 className="text-2xl font-bold text-surface-900">Tools & Equipment</h1>
          <p className="text-sm text-surface-500 mt-1">Track high-value tools, vehicles, and their assignments.</p>
        </div>
        <Button>
          <Plus className="w-4 h-4" />
          Add Tool
        </Button>
      </div>

      <Card>
        <div className="p-4 border-b border-surface-200 flex flex-col sm:flex-row gap-4 items-center justify-between bg-surface-50 rounded-t-xl">
          <div className="relative w-full sm:w-96">
            <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-surface-400" />
            <input 
              type="text" 
              placeholder="Search tools by name, ID, or assignment..." 
              className="w-full pl-9 pr-4 py-2 border border-surface-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white"
            />
          </div>
          <div className="flex gap-2 w-full sm:w-auto">
            <select className="border border-surface-200 rounded-lg text-sm px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
              <option>All Statuses</option>
              <option>Available</option>
              <option>Assigned</option>
              <option>Maintenance</option>
            </select>
          </div>
        </div>

        {loading ? <SkeletonTable /> : (
        <div className="overflow-x-auto">
          <table className="w-full text-sm text-left">
            <thead className="text-xs text-surface-500 uppercase bg-surface-50 border-b border-surface-200">
              <tr>
                <th className="px-5 py-3 font-semibold">Tool / Asset ID</th>
                <th className="px-5 py-3 font-semibold">Category & Brand</th>
                <th className="px-5 py-3 font-semibold">Status</th>
                <th className="px-5 py-3 font-semibold">Assigned To</th>
                <th className="px-5 py-3 font-semibold">Last Inspection</th>
                <th className="px-5 py-3 font-semibold"></th>
              </tr>
            </thead>
            <tbody className="divide-y divide-surface-100 bg-white">
              {(tools ?? []).map((tool) => (
                <tr key={tool.id} className="hover:bg-surface-50 transition-colors">
                  <td className="px-5 py-4 whitespace-nowrap">
                    <div className="font-semibold text-surface-900 flex items-center gap-2">
                      <PenTool className="w-4 h-4 text-surface-400" />
                      {tool.name}
                    </div>
                    <div className="text-xs font-mono text-surface-500 mt-0.5 ml-6">{tool.id}</div>
                  </td>
                  <td className="px-5 py-4 whitespace-nowrap">
                    <div className="font-medium text-surface-900">{tool.category}</div>
                    <div className="text-xs text-surface-500 mt-0.5">{tool.brand}</div>
                  </td>
                  <td className="px-5 py-4 whitespace-nowrap">
                    <Badge variant={
                      tool.status === 'Available' ? 'success' : 
                      tool.status === 'Assigned' ? 'info' : 
                      tool.status === 'Maintenance' ? 'warning' : 'error'
                    }>
                      {tool.status}
                    </Badge>
                  </td>
                  <td className="px-5 py-4 whitespace-nowrap">
                    {tool.assignedTo ? (
                      <span className="font-medium text-surface-900">{tool.assignedTo}</span>
                    ) : (
                      <span className="text-surface-400 italic">Unassigned</span>
                    )}
                  </td>
                  <td className="px-5 py-4 whitespace-nowrap">
                    <span className="text-surface-600">{tool.lastInspection}</span>
                  </td>
                  <td className="px-5 py-4 whitespace-nowrap text-right">
                    <Button variant="ghost" size="sm" className="text-primary-600">Reassign</Button>
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
