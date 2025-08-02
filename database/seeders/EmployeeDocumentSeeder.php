<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmployeeDocument;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;

class EmployeeDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = Employee::all();
        
        if ($employees->isEmpty()) {
            $this->command->warn('No employees found. Please run EmployeeSeeder first.');
            return;
        }

        $documentTypes = [
            'contract' => [
                'Employment Contract',
                'Non-Disclosure Agreement',
                'Non-Compete Agreement',
                'Offer Letter',
                'Employment Agreement'
            ],
            'certificate' => [
                'Professional Certification',
                'Training Certificate',
                'Skills Certification',
                'Industry Certification',
                'Technical Certification'
            ],
            'id_proof' => [
                'Passport',
                'Driver License',
                'National ID Card',
                'Social Security Card',
                'Work Permit'
            ],
            'resume' => [
                'Resume/CV',
                'Cover Letter',
                'Portfolio',
                'References',
                'Academic Transcript'
            ],
            'other' => [
                'Medical Certificate',
                'Background Check',
                'Reference Letter',
                'Performance Review',
                'Training Record'
            ]
        ];

        $mimeTypes = [
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'image/jpeg' => 'jpg',
            'image/png' => 'png'
        ];

        $documentCount = 0;
        $maxDocuments = 200; // Limit total documents

        foreach ($employees as $employee) {
            if ($documentCount >= $maxDocuments) {
                break;
            }

            // Generate 2-5 documents per employee
            $numDocuments = rand(2, 5);
            
            for ($i = 0; $i < $numDocuments; $i++) {
                if ($documentCount >= $maxDocuments) {
                    break 2;
                }

                $documentType = array_rand($documentTypes);
                $titles = $documentTypes[$documentType];
                $title = $titles[array_rand($titles)];
                
                $mimeType = array_rand($mimeTypes);
                $extension = $mimeTypes[$mimeType];
                
                // Generate fake file path
                $filePath = 'employee-documents/' . $employee->id . '/' . 
                           strtolower(str_replace(' ', '_', $title)) . '_' . 
                           rand(1000, 9999) . '.' . $extension;

                $issueDate = Carbon::now()->subDays(rand(30, 1000));
                $expiryDate = $this->shouldHaveExpiry($documentType) ? 
                             $issueDate->copy()->addYears(rand(1, 5)) : null;

                $status = $this->getDocumentStatus($expiryDate);

                EmployeeDocument::create([
                    'employee_id' => $employee->id,
                    'document_type' => $documentType,
                    'title' => $title,
                    'file_path' => $filePath,
                    'file_size' => rand(100000, 5000000), // 100KB to 5MB
                    'mime_type' => $mimeType,
                    'uploaded_by' => $this->getRandomUploader($employee),
                    'expiry_date' => $expiryDate,
                    'status' => $status,
                ]);

                $documentCount++;
            }
        }

        $this->command->info("Created {$documentCount} employee documents successfully.");
    }

    /**
     * Determine if document should have expiry date.
     */
    private function shouldHaveExpiry(string $documentType): bool
    {
        $expiryTypes = ['certificate', 'id_proof'];
        return in_array($documentType, $expiryTypes);
    }

    /**
     * Get document status based on expiry date.
     */
    private function getDocumentStatus(?Carbon $expiryDate): string
    {
        if (!$expiryDate) {
            return 'active';
        }

        if ($expiryDate->isPast()) {
            return 'expired';
        } elseif ($expiryDate->diffInDays(now()) <= 30) {
            return 'active'; // Will expire soon but still active
        } else {
            return 'active';
        }
    }

    /**
     * Get random uploader for the document.
     */
    private function getRandomUploader(Employee $employee): int
    {
        // Get HR users or managers from the same organization
        $uploaders = User::where('organization_id', $employee->organization_id)
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['hr', 'manager', 'super-admin']);
            })
            ->pluck('id')
            ->toArray();

        if (empty($uploaders)) {
            // Fallback to any user in the organization
            $uploaders = User::where('organization_id', $employee->organization_id)
                ->pluck('id')
                ->toArray();
        }

        return $uploaders[array_rand($uploaders)] ?? 1;
    }
} 