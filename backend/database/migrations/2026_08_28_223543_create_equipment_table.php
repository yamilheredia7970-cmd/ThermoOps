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
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            // Denormalized alongside location_id (matches the frontend's Equipment
            // type and lets the API filter by customer without a location join).
            // Kept in sync with location.customer_id server-side; never client-set.
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('brand');
            $table->string('model');
            $table->string('serial_number')->unique();
            $table->date('installation_date')->nullable();
            $table->date('warranty_expiration')->nullable();
            $table->enum('status', ['Good', 'Attention', 'Critical'])->default('Good');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
