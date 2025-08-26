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
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->integer('default_days')->default(0);
            $table->string('color', 7)->default('#3B82F6'); // Hex color code
            $table->boolean('requires_approval')->default(true);
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['name', 'organization_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
}; 