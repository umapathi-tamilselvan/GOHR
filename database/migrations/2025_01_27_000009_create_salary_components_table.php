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
        Schema::create('salary_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained()->onDelete('cascade');
            $table->string('component_type'); // basic, da, hra, ta, medical, bonus, etc.
            $table->decimal('amount', 10, 2);
            $table->boolean('is_taxable')->default(true);
            $table->string('description')->nullable();
            $table->string('calculation_basis')->nullable(); // percentage, fixed, formula
            $table->decimal('calculation_value', 10, 2)->nullable();
            $table->timestamps();
            
            $table->index(['payroll_id', 'component_type']);
            $table->index(['is_taxable']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_components');
    }
}; 