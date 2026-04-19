<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Warranty;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('warranty_inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Warranty::class)->constrained()->cascadeOnDelete();
            $table->text('message');
            $table->enum('status', ['open', 'pending', 'in-progress', 'closed', 'resolved', 'replaced'])->index();
            $table->json('attachments')->nullable();
            $table->timestamps();

            // index the created_at for the dashboard apex chart for faster fetch
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warranty_inquiries');
    }
};
