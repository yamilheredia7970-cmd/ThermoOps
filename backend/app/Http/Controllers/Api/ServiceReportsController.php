<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SignServiceReportRequest;
use App\Http\Requests\StoreServiceReportRequest;
use App\Http\Resources\ServiceReportResource;
use App\Models\ServiceReport;
use App\Models\WorkOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ServiceReportsController extends Controller
{
    private const EAGER_LOAD = ['customer:id,name', 'location:id,name,address', 'technician:id,name'];

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', ServiceReport::class);

        $reports = ServiceReport::query()
            ->with(self::EAGER_LOAD)
            ->when(
                ! $request->user()->hasAnyRole(['Admin', 'Dispatcher']),
                fn ($query) => $query->where('technician_id', $request->user()->id)
            )
            ->latest('created_at')
            ->paginate($request->integer('per_page', 25));

        return ServiceReportResource::collection($reports);
    }

    public function show(ServiceReport $serviceReport): ServiceReportResource
    {
        $this->authorize('view', $serviceReport);

        return new ServiceReportResource($serviceReport->load([...self::EAGER_LOAD, 'attachments']));
    }

    /**
     * Snapshots the completed work order's billing lines and service type
     * onto the report, so it keeps documenting them even if the work order
     * changes afterward.
     */
    public function store(StoreServiceReportRequest $request): JsonResponse
    {
        $workOrder = WorkOrder::with('lineItems')->findOrFail($request->validated('work_order_id'));

        $subtotal = round($workOrder->lineItems->sum(fn ($lineItem) => $lineItem->subtotal()), 2);

        $report = ServiceReport::create([
            'work_order_id' => $workOrder->id,
            'customer_id' => $workOrder->customer_id,
            'location_id' => $workOrder->location_id,
            'technician_id' => $workOrder->technician_id,
            'type' => $workOrder->service_type,
            'status' => 'Pending Signature',
            'subtotal' => $subtotal,
            'tax' => 0,
            'total' => $subtotal,
        ]);

        return (new ServiceReportResource($report->load(self::EAGER_LOAD)))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Attaches the signature image, marks the report Signed, and freezes a
     * PDF copy that includes it — the PDF served afterward is this stored
     * artifact, not a live re-render.
     */
    public function sign(SignServiceReportRequest $request, ServiceReport $serviceReport): ServiceReportResource
    {
        $path = $request->file('signature')->store('signatures', 'public');

        $serviceReport->attachments()->create([
            'type' => 'signature',
            'disk' => 'public',
            'path' => $path,
            'original_filename' => $request->file('signature')->getClientOriginalName(),
            'uploaded_by' => $request->user()->id,
        ]);

        $serviceReport->status = 'Signed';
        $serviceReport->signed_at = now();
        $serviceReport->save();

        $serviceReport->load([...self::EAGER_LOAD, 'attachments', 'workOrder.lineItems']);
        $serviceReport->pdf_path = $this->generateAndStorePdf($serviceReport);
        $serviceReport->save();

        return new ServiceReportResource($serviceReport);
    }

    public function downloadPdf(ServiceReport $serviceReport): SymfonyResponse
    {
        $this->authorize('view', $serviceReport);

        if ($serviceReport->status === 'Signed' && $serviceReport->pdf_path) {
            return Storage::disk('public')->download($serviceReport->pdf_path, "service-report-{$serviceReport->id}.pdf");
        }

        $serviceReport->load([...self::EAGER_LOAD, 'attachments', 'workOrder.lineItems']);

        return $this->renderPdf($serviceReport)->download("service-report-{$serviceReport->id}.pdf");
    }

    public function destroy(ServiceReport $serviceReport): Response
    {
        $this->authorize('delete', $serviceReport);

        $serviceReport->delete();

        return response()->noContent();
    }

    private function generateAndStorePdf(ServiceReport $serviceReport): string
    {
        $path = "service-reports/report-{$serviceReport->id}.pdf";
        Storage::disk('public')->put($path, $this->renderPdf($serviceReport)->output());

        return $path;
    }

    private function renderPdf(ServiceReport $serviceReport): PdfDocument
    {
        $signature = $serviceReport->signature();

        return Pdf::loadView('pdfs.service-report', [
            'report' => $serviceReport,
            'signaturePath' => $signature ? Storage::disk($signature->disk)->path($signature->path) : null,
        ]);
    }
}
