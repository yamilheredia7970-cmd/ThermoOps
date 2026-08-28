import React from 'react';
import { Package, Search, Filter, ArrowDown, ArrowUp, AlertCircle, Plus } from 'lucide-react';
import { Card, Badge, Button } from '../components/ui';
import { mockInventory } from '../data/mockData';

export function Inventory() {
  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 className="text-2xl font-bold text-surface-900">Inventory</h1>
          <p className="text-sm text-surface-500 mt-1">Manage parts, refrigerants, and consumables.</p>
        </div>
        <div className="flex gap-2">
          <Button variant="outline">Receive Stock</Button>
          <Button>
            <Plus className="w-4 h-4" />
            Add Item
          </Button>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <Card>
          <div className="p-5 flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-surface-500 mb-1">Total Items in Catalog</p>
              <h3 className="text-2xl font-bold text-surface-900">1,248</h3>
            </div>
            <div className="w-10 h-10 bg-primary-50 rounded-lg flex items-center justify-center text-primary-600">
              <Package className="w-5 h-5" />
            </div>
          </div>
        </Card>
        
        <Card>
          <div className="p-5 flex items-center justify-between border-l-4 border-warning-500">
            <div>
              <p className="text-sm font-medium text-surface-500 mb-1">Low Stock Alerts</p>
              <h3 className="text-2xl font-bold text-surface-900">12</h3>
            </div>
            <div className="w-10 h-10 bg-warning-50 rounded-lg flex items-center justify-center text-warning-600">
              <AlertCircle className="w-5 h-5" />
            </div>
          </div>
        </Card>

        <Card>
          <div className="p-5 flex items-center justify-between border-l-4 border-error-500">
            <div>
              <p className="text-sm font-medium text-surface-500 mb-1">Out of Stock</p>
              <h3 className="text-2xl font-bold text-surface-900">4</h3>
            </div>
            <div className="w-10 h-10 bg-error-50 rounded-lg flex items-center justify-center text-error-600">
              <ArrowDown className="w-5 h-5" />
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
              placeholder="Search by part name or SKU..." 
              className="w-full pl-9 pr-4 py-2 border border-surface-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>
          <div className="flex gap-2 w-full sm:w-auto">
            <select className="border border-surface-200 rounded-lg text-sm px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
              <option>All Categories</option>
              <option>Refrigerants</option>
              <option>Motors</option>
              <option>Electrical</option>
              <option>Filters</option>
              <option>Valves</option>
            </select>
            <Button variant="outline" className="px-3">
              <Filter className="w-4 h-4" />
              Filters
            </Button>
          </div>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-sm text-left">
            <thead className="text-xs text-surface-500 uppercase bg-surface-50 border-b border-surface-200">
              <tr>
                <th className="px-5 py-3 font-semibold">Part Info</th>
                <th className="px-5 py-3 font-semibold">Category</th>
                <th className="px-5 py-3 font-semibold text-right">Available</th>
                <th className="px-5 py-3 font-semibold text-right">Reserved</th>
                <th className="px-5 py-3 font-semibold text-right">Threshold</th>
                <th className="px-5 py-3 font-semibold">Status</th>
                <th className="px-5 py-3 font-semibold"></th>
              </tr>
            </thead>
            <tbody className="divide-y divide-surface-100 bg-white">
              {mockInventory.map((item) => {
                const stockRatio = item.availableStock / Math.max(item.lowStockThreshold, 1);
                
                return (
                  <tr key={item.id} className="hover:bg-surface-50 transition-colors cursor-pointer">
                    <td className="px-5 py-4 whitespace-nowrap">
                      <div className="font-semibold text-surface-900">{item.partName}</div>
                      <div className="text-xs font-mono text-surface-500 mt-0.5">{item.sku}</div>
                    </td>
                    <td className="px-5 py-4">
                      <span className="text-surface-700">{item.category}</span>
                    </td>
                    <td className="px-5 py-4 text-right">
                      <div className="font-mono text-base font-medium text-surface-900">{item.availableStock}</div>
                    </td>
                    <td className="px-5 py-4 text-right">
                      <div className="font-mono text-surface-500">{item.reserved}</div>
                    </td>
                    <td className="px-5 py-4 text-right">
                      <div className="font-mono text-surface-400">{item.lowStockThreshold}</div>
                    </td>
                    <td className="px-5 py-4 whitespace-nowrap">
                      <Badge variant={
                        item.status === 'In Stock' ? 'success' : 
                        item.status === 'Out of Stock' ? 'error' : 'warning'
                      }>
                        {item.status}
                      </Badge>
                      
                      {item.status !== 'In Stock' && (
                        <div className="mt-2 w-full bg-surface-200 h-1.5 rounded-full overflow-hidden">
                          <div 
                            className={`h-full ${item.status === 'Out of Stock' ? 'bg-error-500' : 'bg-warning-500'}`} 
                            style={{ width: `${Math.min(stockRatio * 100, 100)}%` }}
                          />
                        </div>
                      )}
                    </td>
                    <td className="px-5 py-4 whitespace-nowrap text-right">
                      <Button variant="ghost" size="sm" className="text-primary-600 hover:text-primary-700 hover:bg-primary-50">
                        Restock
                      </Button>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      </Card>
    </div>
  );
}
