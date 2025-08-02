<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\EmployeeProfile;
use App\Models\User;
use App\Models\Organization;
use Carbon\Carbon;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organizations = Organization::all();
        
        if ($organizations->isEmpty()) {
            $this->command->warn('No organizations found. Please run OrganizationSeeder first.');
            return;
        }

        $users = User::whereDoesntHave('employee')->get();
        
        if ($users->isEmpty()) {
            $this->command->warn('No users found without employee records. Please run UserSeeder first.');
            return;
        }

        $departments = [
            'Human Resources',
            'Information Technology',
            'Marketing',
            'Sales',
            'Finance',
            'Operations',
            'Customer Support',
            'Research & Development',
            'Legal',
            'Administration'
        ];

        $positions = [
            'HR Manager',
            'HR Specialist',
            'Software Engineer',
            'Senior Developer',
            'Marketing Manager',
            'Marketing Specialist',
            'Sales Representative',
            'Sales Manager',
            'Financial Analyst',
            'Accountant',
            'Operations Manager',
            'Customer Support Specialist',
            'Research Analyst',
            'Legal Counsel',
            'Administrative Assistant'
        ];

        $employeeCount = 0;
        $maxEmployees = min(50, $users->count()); // Limit to 50 employees or available users

        foreach ($organizations as $organization) {
            $organizationUsers = $users->where('organization_id', $organization->id);
            
            if ($organizationUsers->isEmpty()) {
                continue;
            }

            // Get managers for this organization
            $managers = User::where('organization_id', $organization->id)
                ->whereHas('roles', function ($query) {
                    $query->whereIn('name', ['manager', 'hr']);
                })
                ->get();

            foreach ($organizationUsers->take(15) as $user) { // Max 15 employees per organization
                if ($employeeCount >= $maxEmployees) {
                    break 2;
                }

                $hireDate = Carbon::now()->subDays(rand(30, 1000));
                $status = $this->getRandomStatus($hireDate);
                $terminationDate = $status === 'terminated' ? $hireDate->copy()->addDays(rand(100, 500)) : null;

                $employee = Employee::create([
                    'user_id' => $user->id,
                    'employee_id' => $this->generateUniqueEmployeeId($organization->id),
                    'department' => $departments[array_rand($departments)],
                    'position' => $positions[array_rand($positions)],
                    'hire_date' => $hireDate,
                    'termination_date' => $terminationDate,
                    'status' => $status,
                    'salary' => $this->generateSalary(),
                    'manager_id' => $managers->isNotEmpty() ? $managers->random()->id : null,
                    'organization_id' => $organization->id,
                ]);

                // Create employee profile
                $this->createEmployeeProfile($employee);

                $employeeCount++;
            }
        }

        $this->command->info("Created {$employeeCount} employees successfully.");
    }

    /**
     * Generate a unique employee ID.
     */
    private function generateUniqueEmployeeId(int $organizationId): string
    {
        do {
            $sequence = rand(1, 999);
            $employeeId = sprintf('EMP%03d%03d', $organizationId, $sequence);
        } while (Employee::where('employee_id', $employeeId)->exists());

        return $employeeId;
    }

    /**
     * Get random employee status based on hire date.
     */
    private function getRandomStatus(Carbon $hireDate): string
    {
        $daysSinceHire = $hireDate->diffInDays(now());
        
        if ($daysSinceHire < 30) {
            return 'probation';
        } elseif ($daysSinceHire > 365 && rand(1, 100) <= 5) {
            return 'terminated';
        } elseif (rand(1, 100) <= 3) {
            return 'on_leave';
        } else {
            return 'active';
        }
    }

    /**
     * Generate realistic salary based on position.
     */
    private function generateSalary(): float
    {
        $baseSalary = rand(30000, 120000);
        return round($baseSalary, 2);
    }

    /**
     * Create employee profile with sample data.
     */
    private function createEmployeeProfile(Employee $employee): void
    {
        $genders = ['male', 'female', 'other'];
        $maritalStatuses = ['single', 'married', 'divorced', 'widowed'];
        $nationalities = ['US', 'UK', 'Canada', 'Australia', 'Germany', 'France', 'Japan', 'India', 'China', 'Brazil'];

        $profile = EmployeeProfile::create([
            'employee_id' => $employee->id,
            'date_of_birth' => Carbon::now()->subYears(rand(22, 65))->subDays(rand(1, 365)),
            'gender' => $genders[array_rand($genders)],
            'marital_status' => $maritalStatuses[array_rand($maritalStatuses)],
            'nationality' => $nationalities[array_rand($nationalities)],
            'address' => $this->generateAddress(),
            'phone' => $this->generatePhone(),
            'emergency_contact_name' => fake()->name(),
            'emergency_contact_phone' => $this->generatePhone(),
            'emergency_contact_relationship' => $this->getRandomRelationship(),
            'skills' => $this->generateSkills(),
            'certifications' => $this->generateCertifications(),
            'work_experience' => $this->generateWorkExperience(),
            'education' => $this->generateEducation(),
        ]);
    }

    /**
     * Generate sample address.
     */
    private function generateAddress(): string
    {
        return fake()->streetAddress() . ', ' . fake()->city() . ', ' . fake()->state() . ' ' . fake()->postcode();
    }

    /**
     * Generate sample phone number.
     */
    private function generatePhone(): string
    {
        return '+1-' . rand(200, 999) . '-' . rand(200, 999) . '-' . rand(1000, 9999);
    }

    /**
     * Get random emergency contact relationship.
     */
    private function getRandomRelationship(): string
    {
        $relationships = ['Spouse', 'Parent', 'Sibling', 'Friend', 'Child'];
        return $relationships[array_rand($relationships)];
    }

    /**
     * Generate sample skills.
     */
    private function generateSkills(): array
    {
        $allSkills = [
            'Project Management', 'Leadership', 'Communication', 'Problem Solving',
            'Team Management', 'Strategic Planning', 'Data Analysis', 'Customer Service',
            'Sales', 'Marketing', 'Financial Analysis', 'Accounting', 'Programming',
            'Web Development', 'Database Management', 'Network Administration',
            'Graphic Design', 'Content Creation', 'Social Media Management',
            'Legal Research', 'Contract Management', 'Human Resources',
            'Recruitment', 'Training', 'Performance Management'
        ];

        $numSkills = rand(3, 8);
        $selectedSkills = [];
        $skillKeys = array_rand($allSkills, min($numSkills, count($allSkills)));
        if (!is_array($skillKeys)) {
            $skillKeys = [$skillKeys];
        }
        
        foreach ($skillKeys as $key) {
            $selectedSkills[] = $allSkills[$key];
        }
        
        return $selectedSkills;
    }

    /**
     * Generate sample certifications.
     */
    private function generateCertifications(): array
    {
        $certifications = [
            [
                'name' => 'Project Management Professional (PMP)',
                'issuing_organization' => 'Project Management Institute',
                'issue_date' => Carbon::now()->subYears(rand(1, 5))->format('Y-m-d'),
                'expiry_date' => Carbon::now()->addYears(rand(1, 3))->format('Y-m-d'),
            ],
            [
                'name' => 'Certified Public Accountant (CPA)',
                'issuing_organization' => 'American Institute of CPAs',
                'issue_date' => Carbon::now()->subYears(rand(2, 8))->format('Y-m-d'),
                'expiry_date' => Carbon::now()->addYears(rand(1, 2))->format('Y-m-d'),
            ],
            [
                'name' => 'Certified Information Systems Security Professional (CISSP)',
                'issuing_organization' => 'ISC²',
                'issue_date' => Carbon::now()->subYears(rand(1, 4))->format('Y-m-d'),
                'expiry_date' => Carbon::now()->addYears(rand(1, 3))->format('Y-m-d'),
            ],
            [
                'name' => 'Professional in Human Resources (PHR)',
                'issuing_organization' => 'HR Certification Institute',
                'issue_date' => Carbon::now()->subYears(rand(1, 6))->format('Y-m-d'),
                'expiry_date' => Carbon::now()->addYears(rand(1, 2))->format('Y-m-d'),
            ],
        ];

        $numCerts = rand(0, 3);
        if ($numCerts === 0) {
            return [];
        }
        
        $selectedCerts = [];
        $certKeys = array_rand($certifications, min($numCerts, count($certifications)));
        if (!is_array($certKeys)) {
            $certKeys = [$certKeys];
        }
        
        foreach ($certKeys as $key) {
            $selectedCerts[] = $certifications[$key];
        }
        
        return $selectedCerts;
    }

    /**
     * Generate sample work experience.
     */
    private function generateWorkExperience(): array
    {
        $companies = [
            'TechCorp Solutions', 'Global Innovations Inc', 'Digital Dynamics',
            'Future Systems Ltd', 'Creative Solutions', 'Enterprise Partners',
            'Strategic Consulting Group', 'Innovation Labs', 'TechStart Inc',
            'Digital Ventures', 'Smart Solutions', 'NextGen Technologies'
        ];

        $positions = [
            'Software Engineer', 'Senior Developer', 'Project Manager',
            'Business Analyst', 'Marketing Manager', 'Sales Representative',
            'Financial Analyst', 'HR Specialist', 'Operations Manager',
            'Customer Success Manager', 'Product Manager', 'Data Analyst'
        ];

        $experience = [];
        $numJobs = rand(1, 4);

        for ($i = 0; $i < $numJobs; $i++) {
            $startDate = Carbon::now()->subYears(rand(1, 10))->subMonths(rand(0, 11));
            $endDate = $i === $numJobs - 1 ? 'Present' : $startDate->copy()->addYears(rand(1, 5));

            $experience[] = [
                'company' => $companies[array_rand($companies)],
                'position' => $positions[array_rand($positions)],
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate,
                'description' => fake()->paragraph(2),
            ];
        }

        return $experience;
    }

    /**
     * Generate sample education.
     */
    private function generateEducation(): array
    {
        $institutions = [
            'Harvard University', 'Stanford University', 'MIT', 'University of California',
            'New York University', 'University of Michigan', 'University of Texas',
            'University of Illinois', 'University of Washington', 'University of Pennsylvania',
            'Columbia University', 'University of Chicago', 'Yale University',
            'Princeton University', 'University of California, Berkeley'
        ];

        $degrees = [
            'Bachelor of Science in Computer Science',
            'Bachelor of Business Administration',
            'Master of Business Administration',
            'Master of Science in Information Technology',
            'Bachelor of Arts in Marketing',
            'Master of Science in Finance',
            'Bachelor of Science in Engineering',
            'Master of Science in Data Science',
            'Bachelor of Arts in Communications',
            'Master of Science in Project Management'
        ];

        $education = [];
        $numEducation = rand(1, 3);

        for ($i = 0; $i < $numEducation; $i++) {
            $startDate = Carbon::now()->subYears(rand(5, 20))->subMonths(rand(0, 11));
            $endDate = $startDate->copy()->addYears(rand(3, 6));

            $education[] = [
                'institution' => $institutions[array_rand($institutions)],
                'degree' => $degrees[array_rand($degrees)],
                'field_of_study' => fake()->words(2, true),
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'grade' => rand(2, 4) . '.' . rand(0, 9),
            ];
        }

        return $education;
    }
} 