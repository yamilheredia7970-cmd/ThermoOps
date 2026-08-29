import React from 'react';
import { useNavigate } from 'react-router-dom';
import { Search, FileText, Download, Filter } from 'lucide-react';
import { Card, Badge, Button } from '../components/ui';
import { SkeletonTable } from '../components/ui/Skeleton';
import { useApiList } from '../hooks/useApi';
import { ServiceReport } from '../types';
import { formatCurrency } from '../lib/utils';

export function Reports() {
  const navigate = useNavigate();
  const { data: reports, loading } = useApiList<ServiceReport>('/service-reports');
  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 print:hidden">
        <div>
          <h1 className="text-2xl font-bold text-surface-900">Service Reports</h1>
          <p className="text-sm text-surface-500 mt-1">Review, export, and manage finalized job documents.</p>
        </div>
        <Button variant="outline">
          Export All (CSV)
        </Button>
      </div>

      <Card>
        <div className="p-4 border-b border-surface-200 flex flex-col sm:flex-row gap-4 items-center justify-between bg-surface-50 rounded-t-xl">
          <div className="relative w-full sm:w-96">
            <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-surface-400" />
            <input 
              type="text" 
              placeholder="Search reports by ID, customer..." 
              className="w-full pl-9 pr-4 py-2 border border-surface-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white"
            />
          </div>
          <div className="flex gap-2 w-full sm:w-auto">
            <Button variant="outline" className="px-3 bg-white">
              <Filter className="w-4 h-4" />
              Filter by Date
            </Button>
          </div>
        </div>

        {loading ? <SkeletonTable /> : (
        <div className="overflow-x-auto">
          <table className="w-full text-sm text-left">
            <thead className="text-xs text-surface-500 uppercase bg-surface-50 border-b border-surface-200">
              <tr>
                <th className="px-5 py-3 font-semibold">Report ID / Date</th>
                <th className="px-5 py-3 font-semibold">Customer & Location</th>
                <th className="px-5 py-3 font-semibold">Service Type</th>
                <th className="px-5 py-3 font-semibold">Technician</th>
                <th className="px-5 py-3 font-semibold text-right">Amount</th>
                <th className="px-5 py-3 font-semibold text-center">Status</th>
                <th className="px-5 py-3 font-semibold"></th>
              </tr>
            </thead>
            <tbody className="divide-y divide-surface-100 bg-white">
              {(reports ?? []).map((report) => (
                <tr
                  key={report.id}
                  onClick={() => navigate(`/reports/${report.id}`)}
                  className="hover:bg-surface-50 transition-colors cursor-pointer group"
                >
                  <td className="px-5 py-4 whitespace-nowrap">
                    <div className="font-semibold text-surface-900 flex items-center gap-2">
                      <FileText className="w-4 h-4 text-surface-400" />
                      REP-{report.id}
                    </div>
                    <div className="text-xs text-surface-500 mt-0.5 ml-6">{report.date}</div>
                  </td>
                  <td className="px-5 py-4 whitespace-nowrap">
                    <div className="font-medium text-surface-900">{report.customerName}</div>
                    <div className="text-xs text-surface-500 mt-0.5">{report.locationName}</div>
                  </td>
                  <td className="px-5 py-4 whitespace-nowrap">
                    <span className="text-surface-700">{report.type}</span>
                  </td>
                  <td className="px-5 py-4 whitespace-nowrap">
                    <span className="font-medium text-surface-900">{report.technicianName}</span>
                  </td>
                  <td className="px-5 py-4 whitespace-nowrap text-right">
                    <span className="font-medium text-surface-900">{report.amount ? formatCurrency(report.amount) : '-'}</span>
                  </td>
                  <td className="px-5 py-4 whitespace-nowrap text-center">
                    <Badge variant={
                      report.status === 'Signed' ? 'success' : 
                      report.status === 'Pending Signature' ? 'warning' : 'default'
                    }>
                      {report.status}
                    </Badge>
                  </td>
                  <td className="px-5 py-4 whitespace-nowrap text-right">
                    <Button variant="ghost" size="sm" className="text-primary-600 opacity-0 group-hover:opacity-100 transition-opacity">
                      <Download className="w-4 h-4" />
                    </Button>
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
