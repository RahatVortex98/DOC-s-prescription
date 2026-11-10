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
        // 1. Add doctor profile & description fields to tenants (managed permanently by Super Admin)
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('doctor_qualification')->nullable()->after('doctor_name');
            $table->string('doctor_designation')->nullable()->after('doctor_qualification');
            $table->string('specialty')->nullable()->after('doctor_designation');
            $table->text('doctor_description')->nullable()->after('specialty');
            $table->string('brand_color')->nullable()->default('#0891b2')->after('doctor_description');
            $table->string('logo_path')->nullable()->after('brand_color');
            $table->string('signature_path')->nullable()->after('logo_path');
        });

        // 2. Add doctor_bmdc_no and drop hardcoded column defaults on prescriptions
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->string('doctor_bmdc_no')->nullable()->after('doctor_qualification');
            $table->string('doctor_name')->nullable()->change();
            $table->string('doctor_qualification')->nullable()->change();
            $table->string('chamber_name')->nullable()->change();
        });

        // 3. Create prescription_templates table
        Schema::create('prescription_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('specialty')->nullable();
            $table->text('chief_complaints')->nullable();
            $table->text('clinical_findings')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('investigations')->nullable();
            $table->text('advice')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'specialty']);
        });

        // 4. Create drug_kits table
        Schema::create('drug_kits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('specialty')->nullable();
            $table->json('items');
            $table->timestamps();

            $table->index(['tenant_id', 'specialty']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drug_kits');
        Schema::dropIfExists('prescription_templates');

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropColumn('doctor_bmdc_no');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'doctor_qualification',
                'doctor_designation',
                'specialty',
                'doctor_description',
                'brand_color',
                'logo_path',
                'signature_path',
            ]);
        });
    }
};
