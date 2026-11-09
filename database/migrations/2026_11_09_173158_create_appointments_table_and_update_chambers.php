<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chambers', function (Blueprint $table) {
            $table->string('room_number')->nullable()->after('address');
            $table->string('secondary_phone')->nullable()->after('phone');
            $table->string('whatsapp_number')->nullable()->after('secondary_phone');
            $table->string('hospital_affiliation')->nullable()->after('name');
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chamber_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('prescription_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('serial_no')->default(1);
            $table->string('patient_name');
            $table->string('patient_phone')->nullable()->index();
            $table->integer('patient_age');
            $table->enum('patient_gender', ['Male', 'Female', 'Other'])->default('Male');
            $table->date('appointment_date');
            $table->string('appointment_time')->nullable(); // e.g. 05:30 PM
            $table->enum('status', ['waiting', 'with_doctor', 'completed', 'cancelled'])->default('waiting');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['chamber_id', 'appointment_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');

        Schema::table('chambers', function (Blueprint $table) {
            $table->dropColumn([
                'room_number',
                'secondary_phone',
                'whatsapp_number',
                'hospital_affiliation',
            ]);
        });
    }
};
