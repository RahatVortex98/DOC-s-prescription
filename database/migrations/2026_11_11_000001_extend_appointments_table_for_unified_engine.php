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
        Schema::table('appointments', function (Blueprint $table) {
            if (! Schema::hasColumn('appointments', 'booking_source')) {
                $table->string('booking_source', 20)->default('receptionist')->after('status')->index();
            }

            if (! Schema::hasColumn('appointments', 'booking_reference')) {
                $table->string('booking_reference', 30)->nullable()->after('booking_source')->index();
            }

            if (! Schema::hasColumn('appointments', 'confirmation_status')) {
                $table->string('confirmation_status', 20)->default('confirmed')->after('booking_reference')->index();
            }

            if (! Schema::hasColumn('appointments', 'conversation_id')) {
                $table->unsignedBigInteger('conversation_id')->nullable()->after('confirmation_status')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['conversation_id', 'confirmation_status', 'booking_reference', 'booking_source'] as $col) {
                if (Schema::hasColumn('appointments', $col)) {
                    $columnsToDrop[] = $col;
                }
            }

            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
