<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ServiceReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'workOrderId' => $this->work_order_id,
            'date' => $this->created_at->toDateString(),
            'customerName' => $this->whenLoaded('customer', fn () => $this->customer->name),
            'locationName' => $this->whenLoaded('location', fn () => $this->location->name),
            'technicianName' => $this->whenLoaded('technician', fn () => $this->technician?->name),
            'type' => $this->type,
            'status' => $this->status,
            'subtotal' => (float) $this->subtotal,
            'tax' => (float) $this->tax,
            'amount' => (float) $this->total,
            'signedAt' => $this->signed_at,
            'signatureUrl' => $this->whenLoaded('attachments', fn () => $this->signature()?->url()),
            'pdfUrl' => $this->pdf_path ? Storage::disk('public')->url($this->pdf_path) : null,
        ];
    }
}
