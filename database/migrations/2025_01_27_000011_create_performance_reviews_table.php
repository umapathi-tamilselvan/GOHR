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
        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');
            $table->string('review_period'); // 2024-Q1, 2024-Annual, etc.
            $table->enum('review_type', ['annual', 'quarterly', 'project', 'probation']);
            $table->decimal('overall_rating', 3, 2); // 1.00 to 5.00
            $table->enum('status', ['draft', 'in_progress', 'completed', 'approved'])->default('draft');
            $table->date('review_date');
            $table->date('next_review_date')->nullable();
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('action_plan')->nullable();
            $table->text('manager_comments')->nullable();
            $table->text('employee_comments')->nullable();
            $table->boolean('is_acknowledged')->default(false);
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['employee_id', 'review_period']);
            $table->index(['reviewer_id', 'status']);
            $table->index(['review_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_reviews');
    }
}; 