<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // add archieve in the status
        DB::statement("
            ALTER TABLE warranties
            MODIFY status ENUM(
                'active',
                'pending',
                'near-expiry',
                'expired',
                'archived'
            ) NOT NULL
        ");

        // add archived date
        Schema::table('warranties', function (Blueprint $table) {
            $table->timestamp('archived_at')
                ->nullable()
                ->after('status')
                ->index();
        });

        // remove user_id in the inquiries
        Schema::table('warranty_inquiries', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE warranties
            MODIFY status ENUM(
                'active',
                'pending',
                'near-expiry',
                'expired'
            ) NOT NULL
        ");

        Schema::table('warranties', function (Blueprint $table) {
            $table->dropColumn('archived_at');
        });

        Schema::table('warranty_inquiries', function (Blueprint $table) {

            $table->foreignId('user_id')
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();
        });
    }
};
