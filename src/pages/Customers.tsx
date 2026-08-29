import React from 'react';
import { useNavigate } from 'react-router-dom';
import { Search, Plus, MoreHorizontal, Building2, Users } from 'lucide-react';
import { Card, Badge, Button } from '../components/ui';
import { SkeletonTable } from '../components/ui/Skeleton';
import { useApiList } from '../hooks/useApi';
import { Customer } from '../types';

export function Customers() {
  const navigate = useNavigate();
  const { data: customers, loading } = useApiList<Customer>('/customers');
  const commercialCount = customers?.filter(c => c.type === 'Commercial').length ?? 0;
  const residentialCount = customers?.filter(c => c.type === 'Residential').length ?? 0;

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 print:hidden">
        <div>
          <h1 className="text-2xl font-bold text-surface-900">Customers</h1>
          <p className="text-sm text-surface-500 mt-1">Manage client accounts and contracts.</p>
        </div>
        <div className="flex gap-2">
          <Button variant="outline" className="bg-white" onClick={() => window.print()}>
            Print View
          </Button>
          <Button>
            <Plus className="w-4 h-4 mr-2" />
            Add Customer
          </Button>
        </div>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <Card className="p-4 flex items-center gap-4">
          <div className="w-12 h-12 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
            <Building2 className="w-6 h-6" />
          </div>
          <div>
            <p className="text-sm font-medium text-surface-500">Commercial Clients</p>
            <h3 className="text-2xl font-bold text-surface-900">{commercialCount}</h3>
          </div>
        </Card>
        <Card className="p-4 flex items-center gap-4">
          <div className="w-12 h-12 bg-accent-50 rounded-xl flex items-center justify-center text-accent-600">
            <Users className="w-6 h-6" />
          </div>
          <div>
            <p className="text-sm font-medium text-surface-500">Residential Clients</p>
            <h3 className="text-2xl font-bold text-surface-900">{residentialCount}</h3>
          </div>
        </Card>
      </div>

      <Card>
        <div className="p-4 border-b border-surface-200 flex flex-col sm:flex-row gap-4 items-center justify-between bg-surface-50 rounded-t-xl">
          <div className="relative w-full sm:w-96">
            <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-surface-400" />
            <input 
              type="text" 
              placeholder="Search customers..." 
              className="w-full pl-9 pr-4 py-2 border border-surface-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white"
            />
          </div>
          <div className="flex gap-2 w-full sm:w-auto">
            <select className="border border-surface-200 rounded-lg text-sm px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
              <option>All Types</option>
              <option>Commercial</option>
              <option>Residential</option>
              <option>Industrial</option>
            </select>
          </div>
        </div>

        {loading ? <SkeletonTable /> : (
        <div className="overflow-x-auto">
          <table className="w-full text-sm text-left">
            <thead className="text-xs text-surface-500 uppercase bg-surface-50 border-b border-surface-200">
              <tr>
                <th className="px-5 py-3 font-semibold">Customer Name</th>
                <th className="px-5 py-3 font-semibold">Type</th>
                <th className="px-5 py-3 font-semibold text-center">Locations</th>
                <th className="px-5 py-3 font-semibold text-center">Equipment</th>
                <th className="px-5 py-3 font-semibold text-center">Open WOs</th>
                <th className="px-5 py-3 font-semibold">Status</th>
                <th className="px-5 py-3 font-semibold"></th>
              </tr>
            </thead>
            <tbody className="divide-y divide-surface-100 bg-white">
              {(customers ?? []).map((customer) => (
                <tr 
                  key={customer.id} 
                  onClick={() => navigate(`/customers/${customer.id}`)}
                  className="hover:bg-surface-50 transition-colors cursor-pointer group"
                >
                  <td className="px-5 py-4 whitespace-nowrap">
                    <div className="font-semibold text-surface-900">{customer.name}</div>
                    <div className="text-xs text-surface-500 mt-0.5">Since {new Date(customer.since).getFullYear()}</div>
                  </td>
                  <td className="px-5 py-4 whitespace-nowrap">
                    <span className="text-surface-700 font-medium">{customer.type}</span>
                  </td>
                  <td className="px-5 py-4 text-center font-medium text-surface-900">{customer.locationsCount}</td>
                  <td className="px-5 py-4 text-center font-medium text-surface-900">{customer.equipmentCount}</td>
                  <td className="px-5 py-4 text-center">
                    {customer.activeWorkOrders > 0 ? (
                      <Badge variant="warning">{customer.activeWorkOrders}</Badge>
                    ) : (
                      <span className="text-surface-400">0</span>
                    )}
                  </td>
                  <td className="px-5 py-4 whitespace-nowrap">
                    <Badge variant={customer.status === 'Active' ? 'success' : 'default'}>{customer.status}</Badge>
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
