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
        Schema::create('performance_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performance_review_id')->constrained()->onDelete('cascade');
            $table->string('goal_title');
            $table->text('goal_description');
            $table->enum('goal_type', ['performance', 'development', 'team', 'project']);
            $table->date('target_date');
            $table->text('achievement_criteria');
            $table->decimal('weightage', 5, 2); // 0.00 to 100.00
            $table->enum('status', ['not_started', 'in_progress', 'completed', 'overdue'])->default('not_started');
            $table->decimal('achievement_percentage', 5, 2)->default(0); // 0.00 to 100.00
            $table->text('progress_notes')->nullable();
            $table->text('achievement_evidence')->nullable();
            $table->date('completed_date')->nullable();
            $table->timestamps();
            
            $table->index(['performance_review_id', 'goal_type']);
            $table->index(['status', 'target_date']);
            $table->index(['achievement_percentage']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_goals');
    }
}; 