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
        if (! Schema::hasTable('tenants')) {
            Schema::create('tenants', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->string('doctor_name');
                $table->string('bmdc_no')->nullable();
                $table->enum('status', ['trial', 'active', 'suspended'])->default('trial');
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('users', 'tenant_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
                $table->enum('role', ['super_admin', 'doctor', 'assistant'])->default('doctor');
            });
        }

        if (! Schema::hasColumn('chambers', 'tenant_id')) {
            Schema::table('chambers', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            });
        }

        if (! Schema::hasColumn('patients', 'tenant_id')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            });
        }

        if (! Schema::hasColumn('prescriptions', 'tenant_id')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
                $table->dropUnique(['prescription_no']);
                $table->unique(['tenant_id', 'prescription_no']);
            });
        }

        if (! Schema::hasColumn('appointments', 'tenant_id')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
                $table->unique(['chamber_id', 'appointment_date', 'serial_no']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('appointments', 'tenant_id')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropUnique(['chamber_id', 'appointment_date', 'serial_no']);
                $table->dropConstrainedForeignId('tenant_id');
            });
        }

        if (Schema::hasColumn('prescriptions', 'tenant_id')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                $table->dropUnique(['tenant_id', 'prescription_no']);
                $table->unique('prescription_no');
                $table->dropConstrainedForeignId('tenant_id');
            });
        }

        if (Schema::hasColumn('patients', 'tenant_id')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->dropConstrainedForeignId('tenant_id');
            });
        }

        if (Schema::hasColumn('chambers', 'tenant_id')) {
            Schema::table('chambers', function (Blueprint $table) {
                $table->dropConstrainedForeignId('tenant_id');
            });
        }

        if (Schema::hasColumn('users', 'tenant_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('tenant_id');
                $table->dropColumn('role');
            });
        }

        Schema::dropIfExists('tenants');
    }
};
