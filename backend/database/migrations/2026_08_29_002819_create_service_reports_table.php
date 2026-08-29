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
        Schema::create('service_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->unique()->constrained()->cascadeOnDelete();
            // Snapshotted from the work order at generation time, not looked
            // up live: a report must keep documenting who/where/what it was
            // for even if the work order is later reassigned or edited.
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('location_id')->constrained();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type');
            $table->enum('status', ['Draft', 'Pending Signature', 'Signed'])->default('Draft');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->dateTime('signed_at')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_reports');
    }
};
