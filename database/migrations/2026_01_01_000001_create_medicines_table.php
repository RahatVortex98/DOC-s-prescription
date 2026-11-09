<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('brand_name')->index();
            $table->string('generic_name')->index();
            $table->string('dosage_form')->nullable();
            $table->string('strength')->nullable();
            $table->string('manufacturer')->nullable();
            $table->timestamps();

            $table->index(['brand_name', 'generic_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
