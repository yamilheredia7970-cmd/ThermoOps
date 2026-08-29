import React, { useRef, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, Printer, Download, Check, Upload } from 'lucide-react';
import { Card, Button } from '../../components/ui';
import { Skeleton } from '../../components/ui/Skeleton';
import { useApiResource } from '../../hooks/useApi';
import { ServiceReport } from '../../types';
import { formatCurrency } from '../../lib/utils';
import { api } from '../../lib/api';

interface ServiceReportDetail extends ServiceReport {
  subtotal: number;
  tax: number;
  signedAt: string | null;
  signatureUrl: string | null;
  pdfUrl: string | null;
}

export function ReportViewer() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { data: report, loading, reload } = useApiResource<ServiceReportDetail>(id ? `/service-reports/${id}` : null);
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [signing, setSigning] = useState(false);

  const handleSignFile = async (file: File) => {
    if (!id) return;
    setSigning(true);
    try {
      const formData = new FormData();
      formData.append('signature', file);
      await api.upload(`/service-reports/${id}/sign`, formData);
      reload();
    } finally {
      setSigning(false);
    }
  };

  if (loading || !report) {
    return (
      <div className="space-y-6 max-w-4xl mx-auto pb-12">
        <Skeleton className="h-10 w-48" />
        <Skeleton className="h-[600px] w-full rounded-xl" />
      </div>
    );
  }

  return (
    <div className="space-y-6 max-w-4xl mx-auto pb-12">
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-4">
          <Button variant="ghost" size="icon" onClick={() => navigate('/reports')} className="text-surface-500 hover:text-surface-900">
            <ArrowLeft className="w-5 h-5" />
          </Button>
          <div>
            <h1 className="text-2xl font-bold text-surface-900">Digital Report REP-{report.id}</h1>
          </div>
        </div>
        <div className="flex items-center gap-2">
          <Button variant="outline" className="bg-white" onClick={() => window.print()}>
            <Printer className="w-4 h-4 mr-2" /> Print
          </Button>
          <Button onClick={() => window.open(`/api/v1/service-reports/${report.id}/pdf`, '_blank')}>
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
          </div>

          <div className="text-right">
            <h1 className="text-3xl font-bold text-surface-200 uppercase tracking-wider mb-2">Service Report</h1>
            <div className="text-sm space-y-1 text-surface-900">
              <p><span className="text-surface-500 mr-2">Report #:</span> <span className="font-semibold">REP-{report.id}</span></p>
              <p><span className="text-surface-500 mr-2">Date:</span> <span className="font-semibold">{report.date}</span></p>
              <p><span className="text-surface-500 mr-2">Technician:</span> <span className="font-semibold">{report.technicianName ?? 'Unassigned'}</span></p>
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
            {report.status === 'Signed' && (
              <div className="mt-2 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-surface-100 text-sm font-medium text-surface-700">
                <Check className="w-3.5 h-3.5 text-success-600" /> Job Completed
              </div>
            )}
          </div>
        </div>

        {/* Totals */}
        <div className="mb-8">
          <h3 className="text-xs font-bold text-surface-400 uppercase tracking-wider mb-3 border-b border-surface-200 pb-2">Billing Summary</h3>
          <p className="text-sm text-surface-500 mb-3">See the downloaded PDF for the itemized labor and parts breakdown.</p>
          <table className="w-full text-sm">
            <tbody className="divide-y divide-surface-100">
              <tr>
                <td className="py-2 text-surface-700">Subtotal</td>
                <td className="py-2 text-right text-surface-900">{formatCurrency(report.subtotal)}</td>
              </tr>
              <tr>
                <td className="py-2 text-surface-700">Tax</td>
                <td className="py-2 text-right text-surface-900">{formatCurrency(report.tax)}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr>
                <td className="py-4 text-right font-bold text-surface-900">Total Amount</td>
                <td className="py-4 text-right font-bold text-surface-900 text-lg">{formatCurrency(report.amount ?? 0)}</td>
              </tr>
            </tfoot>
          </table>
        </div>

        {/* Signature */}
        <div className="pt-8 border-t border-surface-200">
          <h3 className="text-xs font-bold text-surface-400 uppercase tracking-wider mb-3">Customer Signature</h3>
          {report.signatureUrl ? (
            <div>
              <img src={report.signatureUrl} alt="Signature" className="h-20 border-b border-surface-300" />
              <p className="text-xs text-surface-500 mt-2">
                Signed {report.signedAt ? new Date(report.signedAt).toLocaleString() : ''}
              </p>
            </div>
          ) : (
            <div className="flex items-center gap-3">
              <span className="text-surface-400 italic text-sm">Pending signature...</span>
              <input
                ref={fileInputRef}
                type="file"
                accept="image/png,image/jpeg"
                className="hidden"
                onChange={(e) => e.target.files?.[0] && handleSignFile(e.target.files[0])}
              />
              <Button size="sm" variant="outline" disabled={signing} onClick={() => fileInputRef.current?.click()}>
                <Upload className="w-4 h-4 mr-2" /> {signing ? 'Uploading...' : 'Upload Signature'}
              </Button>
            </div>
          )}
        </div>
      </Card>
    </div>
  );
}
