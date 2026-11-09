<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('patient_id')->unique();
            $table->string('name');
            $table->string('phone')->nullable()->index();
            $table->integer('age');
            $table->enum('gender', ['Male', 'Female', 'Other']);
            $table->timestamps();
        });

        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->string('prescription_no')->unique();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->string('doctor_name')->default('Dr. Mahbubur Rahman');
            $table->string('doctor_qualification')->default('MBBS, BCS (Health), MS (Surgery)');
            $table->string('chamber_name')->default('Bikrify Digital Health Care');
            $table->date('prescription_date');
            $table->text('chief_complaints')->nullable();
            $table->text('clinical_findings')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('investigations')->nullable();
            $table->text('advice')->nullable();
            $table->timestamps();
        });

        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained()->cascadeOnDelete();
            $table->string('type')->nullable();
            $table->string('medicine_name');
            $table->string('generic_name')->nullable();
            $table->string('strength')->nullable();
            $table->string('dosage');
            $table->string('duration');
            $table->string('instructions')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_items');
        Schema::dropIfExists('prescriptions');
        Schema::dropIfExists('patients');
    }
};
