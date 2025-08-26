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
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('holiday_name');
            $table->date('holiday_date');
            $table->enum('holiday_type', ['national', 'regional', 'company', 'optional']);
            $table->string('region')->nullable(); // For regional holidays
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_optional')->default(false);
            $table->integer('year');
            $table->timestamps();
            
            $table->index(['year', 'holiday_type']);
            $table->index(['holiday_date', 'is_active']);
            $table->index(['region', 'holiday_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
}; 