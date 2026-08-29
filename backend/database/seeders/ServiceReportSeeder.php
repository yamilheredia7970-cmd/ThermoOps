<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Location;
use App\Models\ServiceReport;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ServiceReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Mirrors the shape of the frontend's mockReports fixture, generating
     * each report from a real (Completed) work order through the same
     * subtotal-from-line-items logic the API uses, rather than hand-setting
     * an amount. Covers all three statuses.
     */
    public function run(): void
    {
        $marcus = User::where('email', 'mjohnson@thermoops.com')->firstOrFail();
        $david = User::where('email', 'dkim@thermoops.com')->firstOrFail();
        $sarah = User::where('email', 'soconnor@thermoops.com')->firstOrFail();

        // The Inspection work order WorkOrderSeeder already completed, with
        // its real labor + part line items (158.5 subtotal) — sign it.
        $inspectionWorkOrder = WorkOrder::where('status', 'Completed')->firstOrFail();
        $signed = $this->generateReport($inspectionWorkOrder);
        $signed->attachments()->create([
            'type' => 'signature', 'disk' => 'public', 'path' => $this->placeholderSignaturePath($signed->id),
            'original_filename' => 'signature.png', 'uploaded_by' => $marcus->id,
        ]);
        $signed->update(['status' => 'Signed', 'signed_at' => now()->subDay()]);

        // Pending Signature: completed, not yet signed off by the customer.
        $novaLogistics = Customer::where('name', 'Nova Logistics')->firstOrFail();
        $mainWarehouse = Location::where('customer_id', $novaLogistics->id)->firstOrFail();
        $repairWorkOrder = WorkOrder::create([
            'customer_id' => $novaLogistics->id, 'location_id' => $mainWarehouse->id,
            'technician_id' => $david->id, 'created_by' => $marcus->id, 'service_type' => 'Repair',
            'status' => 'Completed', 'priority' => 'Urgent',
            'scheduled_at' => now()->subDay(), 'duration_hours' => 3, 'completed_at' => now()->subDay(),
            'description' => 'Emergency chiller repair.',
        ]);
        $repairWorkOrder->lineItems()->create(['type' => 'labor', 'description' => 'Emergency repair labor', 'quantity' => 3, 'unit_price' => 350]);
        $repairWorkOrder->lineItems()->create(['type' => 'other', 'description' => 'After-hours callout fee', 'quantity' => 1, 'unit_price' => 200]);
        $this->generateReport($repairWorkOrder);

        // Draft: completed, report generated, but no work performed on the
        // billing side yet (subtotal 0) - still being written up.
        $andersonResidence = Customer::where('name', 'Anderson Residence')->firstOrFail();
        $mainHouse = Location::where('customer_id', $andersonResidence->id)->firstOrFail();
        $installWorkOrder = WorkOrder::create([
            'customer_id' => $andersonResidence->id, 'location_id' => $mainHouse->id,
            'technician_id' => $sarah->id, 'created_by' => $marcus->id, 'service_type' => 'Installation',
            'status' => 'Completed', 'priority' => 'Normal',
            'scheduled_at' => now()->subDays(2), 'duration_hours' => 4, 'completed_at' => now()->subDays(2),
            'description' => 'System installation, thermostat configuration.',
        ]);
        $draft = $this->generateReport($installWorkOrder);
        $draft->update(['status' => 'Draft']);
    }

    private function generateReport(WorkOrder $workOrder): ServiceReport
    {
        $workOrder->loadMissing('lineItems');
        $subtotal = round($workOrder->lineItems->sum(fn ($lineItem) => $lineItem->subtotal()), 2);

        return ServiceReport::create([
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
    }

    /**
     * A minimal valid 1x1 PNG, written to storage so the seeded "Signed"
     * report has a real signature attachment to point to.
     */
    private function placeholderSignaturePath(int $reportId): string
    {
        $path = "signatures/seeded-{$reportId}.png";
        $pngBytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
        Storage::disk('public')->put($path, $pngBytes);

        return $path;
    }
}
