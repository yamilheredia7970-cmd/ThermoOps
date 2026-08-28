import React from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, Printer, Download, Check } from 'lucide-react';
import { Card, Button } from '../../components/ui';
import { mockReports } from '../../data/mockData';
import { formatCurrency } from '../../lib/utils';

export function ReportViewer() {
  const { id } = useParams();
  const navigate = useNavigate();
  
  const report = mockReports.find(r => r.id === id) || mockReports[0];

  return (
    <div className="space-y-6 max-w-4xl mx-auto pb-12">
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-4">
          <Button variant="ghost" size="icon" onClick={() => navigate('/reports')} className="text-surface-500 hover:text-surface-900">
            <ArrowLeft className="w-5 h-5" />
          </Button>
          <div>
            <h1 className="text-2xl font-bold text-surface-900">Digital Report {report.id}</h1>
          </div>
        </div>
        <div className="flex items-center gap-2">
          <Button variant="outline" className="bg-white" onClick={() => window.print()}>
            <Printer className="w-4 h-4 mr-2" /> Print
          </Button>
          <Button>
            <Download className="w-4 h-4 mr-2" /> Download PDF
          </Button>
        </div>
      </div>

      {/* A4 Paper Style Container */}
      <Card className="bg-white p-8 md:p-12 shadow-md mx-auto print:shadow-none print:p-0">
        {/* Header */}
        <div className="flex justify-between items-start border-b-2 border-surface-900 pb-8 mb-8">
          <div>
            <div className="flex items-center gap-2 mb-4">
              <div className="w-8 h-8 rounded-lg bg-surface-900 flex items-center justify-center text-white font-bold text-lg">
                T
              </div>
              <div>
                <h2 className="font-bold tracking-wide text-xl leading-none text-surface-900">ThermoOps</h2>
                <p className="text-[9px] text-surface-500 font-bold tracking-widest uppercase mt-1">HVAC Services</p>
              </div>
            </div>
            <div className="text-sm text-surface-500 space-y-1">
              <p>1234 Industrial Pkwy, Suite 100</p>
              <p>Chicago, IL 60601</p>
              <p>contact@thermoops.com</p>
              <p>(555) 123-4567</p>
            </div>
          </div>
          
          <div className="text-right">
            <h1 className="text-3xl font-bold text-surface-200 uppercase tracking-wider mb-2">Service Report</h1>
            <div className="text-sm space-y-1 text-surface-900">
              <p><span className="text-surface-500 mr-2">Report #:</span> <span className="font-semibold">{report.id}</span></p>
              <p><span className="text-surface-500 mr-2">Date:</span> <span className="font-semibold">{report.date}</span></p>
              <p><span className="text-surface-500 mr-2">Technician:</span> <span className="font-semibold">{report.technicianName}</span></p>
            </div>
          </div>
        </div>

        {/* Client Info */}
        <div className="grid grid-cols-2 gap-8 mb-8">
          <div>
            <h3 className="text-xs font-bold text-surface-400 uppercase tracking-wider mb-2">Service Location</h3>
            <p className="font-bold text-surface-900">{report.customerName}</p>
            <p className="text-surface-600 text-sm mt-1">{report.locationName}</p>
          </div>
          <div>
            <h3 className="text-xs font-bold text-surface-400 uppercase tracking-wider mb-2">Service Type</h3>
            <p className="font-semibold text-surface-900">{report.type}</p>
            <div className="mt-2 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-surface-100 text-sm font-medium text-surface-700">
              <Check className="w-3.5 h-3.5 text-success-600" /> Job Completed
            </div>
          </div>
        </div>

        {/* Work Summary */}
        <div className="mb-8">
          <h3 className="text-xs font-bold text-surface-400 uppercase tracking-wider mb-3 border-b border-surface-200 pb-2">Work Summary</h3>
          <div className="bg-surface-50 p-4 rounded-lg text-sm text-surface-700 leading-relaxed border border-surface-100">
            Arrived on site to perform scheduled maintenance. Inspected all electrical connections, tightened loose terminals. Checked refrigerant levels (R410A) - pressures normal. Replaced standard 20x25x1 MERV 11 air filters. Cleaned condenser coils and verified clear condensate drain lines. System operating within manufacturer specifications.
          </div>
        </div>

        {/* Parts & Materials */}
        <div className="mb-8">
          <h3 className="text-xs font-bold text-surface-400 uppercase tracking-wider mb-3 border-b border-surface-200 pb-2">Parts & Materials Used</h3>
          <table className="w-full text-sm">
            <thead className="text-surface-500 border-b border-surface-200">
              <tr>
                <th className="py-2 text-left font-medium">Qty</th>
                <th className="py-2 text-left font-medium">Part Name / Description</th>
                <th className="py-2 text-right font-medium">Unit Price</th>
                <th className="py-2 text-right font-medium">Total</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-surface-100">
              <tr>
                <td className="py-3 text-surface-900">2</td>
                <td className="py-3 text-surface-900">Air Filter 20x25x1 MERV 11</td>
                <td className="py-3 text-right text-surface-600">$15.00</td>
                <td className="py-3 text-right text-surface-900 font-medium">$30.00</td>
              </tr>
              <tr>
                <td className="py-3 text-surface-900">1</td>
                <td className="py-3 text-surface-900">Coil Cleaner (Gallon)</td>
                <td className="py-3 text-right text-surface-600">$45.00</td>
                <td className="py-3 text-right text-surface-900 font-medium">$45.00</td>
              </tr>
              <tr>
                <td className="py-3 text-surface-900">2.5</td>
                <td className="py-3 text-surface-900">Labor (Hours)</td>
                <td className="py-3 text-right text-surface-600">$150.00</td>
                <td className="py-3 text-right text-surface-900 font-medium">$375.00</td>
              </tr>
            </tbody>
            <tfoot>
              <tr>
                <td colSpan={3} className="py-4 text-right font-bold text-surface-900">Total Amount</td>
                <td className="py-4 text-right font-bold text-surface-900 text-lg">{formatCurrency(450.00)}</td>
              </tr>
            </tfoot>
          </table>
        </div>

        {/* Photos Placeholders */}
        <div className="mb-10">
          <h3 className="text-xs font-bold text-surface-400 uppercase tracking-wider mb-3 border-b border-surface-200 pb-2">Site Photos</h3>
          <div className="grid grid-cols-3 gap-4">
            <div className="aspect-video bg-surface-100 rounded border border-surface-200 flex flex-col items-center justify-center text-surface-400 text-xs">
              <span>Photo 1</span>
              <span>(Before Cleaning)</span>
            </div>
            <div className="aspect-video bg-surface-100 rounded border border-surface-200 flex flex-col items-center justify-center text-surface-400 text-xs">
              <span>Photo 2</span>
              <span>(After Cleaning)</span>
            </div>
            <div className="aspect-video bg-surface-100 rounded border border-surface-200 flex flex-col items-center justify-center text-surface-400 text-xs">
              <span>Photo 3</span>
              <span>(Filter Replaced)</span>
            </div>
          </div>
        </div>

        {/* Signatures */}
        <div className="grid grid-cols-2 gap-12 pt-8">
          <div>
            <div className="border-b border-surface-300 h-16 flex items-end pb-2 mb-2">
              <span className="font-medium text-surface-900 italic">{report.technicianName}</span>
            </div>
            <p className="text-xs font-bold text-surface-400 uppercase tracking-wider">Technician Signature</p>
          </div>
          <div>
            <div className="border-b border-surface-300 h-16 flex items-end pb-2 mb-2">
              {report.status === 'Signed' ? (
                <span className="font-medium text-surface-900 italic">John Doe (Client)</span>
              ) : (
                <span className="text-surface-300 italic">Pending Signature...</span>
              )}
            </div>
            <p className="text-xs font-bold text-surface-400 uppercase tracking-wider">Customer Approval</p>
          </div>
        </div>
      </Card>
    </div>
  );
}
