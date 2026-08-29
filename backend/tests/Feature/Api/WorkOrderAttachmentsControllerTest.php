<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WorkOrderAttachmentsControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Admin', 'Dispatcher', 'Technician'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        Storage::fake('public');
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_store_uploads_a_photo_for_the_assigned_technician(): void
    {
        $technician = $this->userWithRole('Technician');
        $workOrder = WorkOrder::factory()->create(['technician_id' => $technician->id]);

        $response = $this->actingAs($technician)->postJson("/api/v1/work-orders/{$workOrder->id}/attachments", [
            'type' => 'photo',
            'file' => UploadedFile::fake()->image('before.jpg'),
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.type', 'photo');
        $this->assertSame(1, $workOrder->attachments()->count());
        Storage::disk('public')->assertExists($workOrder->attachments()->first()->path);
    }

    public function test_store_is_forbidden_for_a_technician_not_assigned_to_the_work_order(): void
    {
        $workOrder = WorkOrder::factory()->create();
        $otherTechnician = $this->userWithRole('Technician');

        $this->actingAs($otherTechnician)->postJson("/api/v1/work-orders/{$workOrder->id}/attachments", [
            'type' => 'photo',
            'file' => UploadedFile::fake()->image('before.jpg'),
        ])->assertForbidden();
    }

    public function test_store_rejects_a_file_that_is_too_large(): void
    {
        $workOrder = WorkOrder::factory()->create();

        $response = $this->actingAs($this->userWithRole('Admin'))->postJson("/api/v1/work-orders/{$workOrder->id}/attachments", [
            'type' => 'photo',
            'file' => UploadedFile::fake()->image('before.jpg')->size(20000),
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('file');
    }

    public function test_destroy_removes_the_attachment_and_its_file(): void
    {
        $workOrder = WorkOrder::factory()->create();
        $attachment = $workOrder->attachments()->create([
            'type' => 'photo', 'disk' => 'public', 'path' => 'work-orders/1/test.jpg',
            'original_filename' => 'test.jpg',
        ]);
        Storage::disk('public')->put($attachment->path, 'fake-contents');

        $this->actingAs($this->userWithRole('Admin'))
            ->deleteJson("/api/v1/work-orders/{$workOrder->id}/attachments/{$attachment->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('attachments', ['id' => $attachment->id]);
        Storage::disk('public')->assertMissing($attachment->path);
    }
}
