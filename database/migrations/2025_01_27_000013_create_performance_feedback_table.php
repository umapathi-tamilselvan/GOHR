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
        Schema::create('performance_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performance_review_id')->constrained()->onDelete('cascade');
            $table->foreignId('feedback_provider_id')->constrained('users')->onDelete('cascade');
            $table->enum('feedback_type', ['self', 'manager', 'peer', 'subordinate', 'customer']);
            $table->boolean('is_anonymous')->default(false);
            $table->text('feedback_text');
            $table->decimal('rating', 3, 2)->nullable(); // 1.00 to 5.00
            $table->json('feedback_questions')->nullable(); // Structured feedback questions
            $table->json('feedback_answers')->nullable(); // Structured feedback answers
            $table->enum('status', ['draft', 'submitted', 'reviewed'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            
            $table->index(['performance_review_id', 'feedback_type']);
            $table->index(['feedback_provider_id', 'status']);
            $table->index(['is_anonymous', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_feedback');
    }
}; 