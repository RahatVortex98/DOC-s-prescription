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
        // 1. Patients: Soft deletes and allergies field
        Schema::table('patients', function (Blueprint $table) {
            $table->text('allergies')->nullable()->after('gender');
            $table->softDeletes();
        });

        // 2. Prescriptions: Soft deletes, status, void tracking, and ULID public_id for secure sharing
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->string('public_id', 36)->nullable()->unique()->after('prescription_no');
            $table->string('status', 20)->default('active')->after('prescription_date');
            $table->timestamp('voided_at')->nullable()->after('status');
            $table->string('void_reason')->nullable()->after('voided_at');
            $table->softDeletes();
        });

        // 3. Appointments: Soft deletes
        Schema::table('appointments', function (Blueprint $table) {
            $table->softDeletes();
        });

        // 4. Audit logs: Immutable clinical audit trail scoped to tenant
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 80); // e.g. prescription.created, prescription.printed, prescription.voided, patient.updated, appointment.deleted
            $table->string('subject_type', 120)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('details')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tenant_id', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['public_id', 'status', 'voided_at', 'void_reason']);
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('allergies');
        });
    }
};
