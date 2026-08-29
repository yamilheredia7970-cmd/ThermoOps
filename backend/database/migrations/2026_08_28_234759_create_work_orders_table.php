<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->foreignId('equipment_id')->nullable()->constrained('equipment')->nullOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->enum('service_type', ['Maintenance', 'Repair', 'Installation', 'Inspection']);
            $table->enum('status', ['Scheduled', 'In Progress', 'On Hold', 'Completed', 'Cancelled'])
                ->default('Scheduled');
            $table->enum('priority', ['Low', 'Normal', 'High', 'Urgent'])->default('Normal');
            // Single instant instead of separate date/time columns: makes the
            // technician double-booking overlap query a plain range comparison.
            $table->dateTime('scheduled_at');
            $table->decimal('duration_hours', 4, 2);
            $table->text('description')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['technician_id', 'scheduled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
