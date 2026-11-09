<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chambers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Chamber / Hospital Name
            $table->string('doctor_name')->default('Dr. Mahbubur Rahman');
            $table->string('doctor_qualification')->default('MBBS, FCPS, MD (Nephrology), BCS (Health)');
            $table->string('doctor_designation')->default('Associate Professor & Senior Consultant');
            $table->string('specialty')->default('Nephrology / CKD Specialist');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('visiting_hours')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->foreignId('chamber_id')->nullable()->constrained('chambers')->nullOnDelete();
            $table->string('doctor_designation')->nullable()->after('doctor_qualification');
            $table->string('chamber_address')->nullable()->after('chamber_name');
            $table->string('chamber_phone')->nullable()->after('chamber_address');
            $table->string('chamber_visiting_hours')->nullable()->after('chamber_phone');
        });
    }

    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('chamber_id');
            $table->dropColumn([
                'doctor_designation',
                'chamber_address',
                'chamber_phone',
                'chamber_visiting_hours',
            ]);
        });

        Schema::dropIfExists('chambers');
    }
};
