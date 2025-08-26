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
        Schema::create('salary_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained()->onDelete('cascade');
            $table->string('deduction_type'); // pf, esi, tds, professional_tax, loan, etc.
            $table->decimal('amount', 10, 2);
            $table->string('description')->nullable();
            $table->string('calculation_basis')->nullable(); // percentage, fixed, formula
            $table->decimal('calculation_value', 10, 2)->nullable();
            $table->boolean('is_statutory')->default(false);
            $table->boolean('is_voluntary')->default(false);
            $table->timestamps();
            
            $table->index(['payroll_id', 'deduction_type']);
            $table->index(['is_statutory', 'is_voluntary']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_deductions');
    }
}; 