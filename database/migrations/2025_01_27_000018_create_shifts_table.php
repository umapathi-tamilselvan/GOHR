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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('break_duration')->default(0); // in minutes
            $table->integer('overtime_threshold')->default(8); // in hours
            $table->integer('grace_period')->default(15); // in minutes
            $table->boolean('is_night_shift')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('color_code')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['is_active', 'is_night_shift']);
            $table->index(['start_time', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
}; 