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
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_posting_id')->constrained()->onDelete('cascade');
            $table->string('applicant_name');
            $table->string('applicant_email');
            $table->string('applicant_phone');
            $table->text('cover_letter')->nullable();
            $table->string('resume_path')->nullable();
            $table->string('resume_filename')->nullable();
            $table->enum('status', ['applied', 'screening', 'interview_scheduled', 'interviewed', 'shortlisted', 'rejected', 'hired'])->default('applied');
            $table->enum('source', ['company_website', 'job_board', 'social_media', 'employee_referral', 'direct_application'])->default('company_website');
            $table->text('screening_notes')->nullable();
            $table->text('interview_notes')->nullable();
            $table->decimal('rating', 3, 2)->nullable(); // 1.00 to 5.00
            $table->text('feedback')->nullable();
            $table->date('application_date');
            $table->date('screening_date')->nullable();
            $table->date('interview_date')->nullable();
            $table->date('decision_date')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['job_posting_id', 'status']);
            $table->index(['applicant_email', 'status']);
            $table->index(['status', 'application_date']);
            $table->index(['source', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
}; 