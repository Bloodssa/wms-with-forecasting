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
        Schema::create('warranty_service_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warranty_inquiries_id')->constrained('warranty_inquiries')->cascadeOnDelete();
            // user id of the one who resolved the inquiry
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('service_type', ['repair', 'replacement', 'inspection', 'maintenance', 'other'])
                ->default('repair')
                ->index();
            $table->decimal('parts_cost', 12, 2)->nullable();
            $table->decimal('labor_cost', 12, 2)->nullable();
            $table->decimal('total_cost', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warranty_service_records');
    }
};
