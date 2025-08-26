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
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->string('job_title');
            $table->text('job_description');
            $table->text('job_requirements');
            $table->string('department');
            $table->string('location');
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'intern']);
            $table->enum('experience_level', ['entry', 'mid', 'senior', 'executive']);
            $table->integer('min_experience_years')->default(0);
            $table->integer('max_experience_years')->nullable();
            $table->decimal('min_salary', 10, 2)->nullable();
            $table->decimal('max_salary', 10, 2)->nullable();
            $table->enum('status', ['draft', 'published', 'closed', 'archived'])->default('draft');
            $table->date('published_date')->nullable();
            $table->date('closing_date')->nullable();
            $table->integer('positions_available')->default(1);
            $table->text('benefits')->nullable();
            $table->text('application_instructions')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'department']);
            $table->index(['employment_type', 'experience_level']);
            $table->index(['published_date', 'closing_date']);
            $table->index(['is_featured', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
}; 