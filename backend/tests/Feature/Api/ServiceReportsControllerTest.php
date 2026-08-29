<?php

namespace Tests\Feature\Api;

use App\Models\ServiceReport;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ServiceReportsControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Admin', 'Dispatcher', 'Technician'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_index_scopes_results_to_a_technicians_own_reports(): void
    {
        $technician = $this->userWithRole('Technician');
        $own = ServiceReport::factory()->create(['technician_id' => $technician->id]);
        ServiceReport::factory()->create();

        $response = $this->actingAs($technician)->getJson('/api/v1/service-reports');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $own->id);
    }

    public function test_store_snapshots_the_total_from_the_work_orders_line_items(): void
    {
        $workOrder = WorkOrder::factory()->create(['status' => 'Completed', 'service_type' => 'Maintenance']);
        $workOrder->lineItems()->create(['type' => 'labor', 'description' => 'Labor', 'quantity' => 2, 'unit_price' => 100]);

        $response = $this->actingAs($this->userWithRole('Admin'))->postJson('/api/v1/service-reports', [
            'work_order_id' => $workOrder->id,
        ]);

        $response->assertCreated();
        // JSON doesn't distinguish 200 from 200.0, so the decoded value is an int.
        $response->assertJsonPath('data.amount', 200);
        $response->assertJsonPath('data.status', 'Pending Signature');
        $response->assertJsonPath('data.type', 'Maintenance');
    }

    public function test_store_rejects_a_work_order_that_is_not_completed(): void
    {
        $workOrder = WorkOrder::factory()->create(['status' => 'Scheduled']);

        $response = $this->actingAs($this->userWithRole('Admin'))->postJson('/api/v1/service-reports', [
            'work_order_id' => $workOrder->id,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('work_order_id');
    }

    public function test_store_rejects_a_second_report_for_the_same_work_order(): void
    {
        $existing = ServiceReport::factory()->create();

        $response = $this->actingAs($this->userWithRole('Admin'))->postJson('/api/v1/service-reports', [
            'work_order_id' => $existing->work_order_id,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('work_order_id');
    }

    public function test_store_is_forbidden_for_a_technician_not_assigned_to_the_work_order(): void
    {
        $workOrder = WorkOrder::factory()->create(['status' => 'Completed']);
        $otherTechnician = $this->userWithRole('Technician');

        $this->actingAs($otherTechnician)->postJson('/api/v1/service-reports', [
            'work_order_id' => $workOrder->id,
        ])->assertForbidden();
    }

    public function test_sign_attaches_a_signature_and_marks_the_report_signed(): void
    {
        $report = ServiceReport::factory()->create(['status' => 'Pending Signature']);

        $response = $this->actingAs($this->userWithRole('Admin'))->postJson("/api/v1/service-reports/{$report->id}/sign", [
            'signature' => UploadedFile::fake()->image('signature.png', 200, 80),
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.status', 'Signed');
        $this->assertNotNull($response->json('data.signatureUrl'));
        $this->assertNotNull($report->fresh()->signed_at);
        $this->assertNotNull($report->fresh()->pdf_path);
    }

    public function test_sign_rejects_a_non_image_file(): void
    {
        $report = ServiceReport::factory()->create(['status' => 'Pending Signature']);

        $response = $this->actingAs($this->userWithRole('Admin'))->postJson("/api/v1/service-reports/{$report->id}/sign", [
            'signature' => UploadedFile::fake()->create('signature.pdf', 10, 'application/pdf'),
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('signature');
    }

    public function test_sign_is_forbidden_once_already_signed(): void
    {
        $report = ServiceReport::factory()->create(['status' => 'Signed', 'signed_at' => now()]);

        $this->actingAs($this->userWithRole('Admin'))->postJson("/api/v1/service-reports/{$report->id}/sign", [
            'signature' => UploadedFile::fake()->image('signature.png'),
        ])->assertForbidden();
    }

    public function test_download_pdf_returns_a_pdf_response(): void
    {
        $report = ServiceReport::factory()->create(['status' => 'Pending Signature']);

        $response = $this->actingAs($this->userWithRole('Admin'))->get("/api/v1/service-reports/{$report->id}/pdf");

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_destroy_is_forbidden_for_a_dispatcher(): void
    {
        $report = ServiceReport::factory()->create();

        $this->actingAs($this->userWithRole('Dispatcher'))->deleteJson("/api/v1/service-reports/{$report->id}")->assertForbidden();
    }
}
