<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\EmployeeProfile;
use App\Models\FamilyMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class EmployeeTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $organization;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create organization
        $this->organization = Organization::create([
            'name' => 'Test Organization',
            'description' => 'Test organization for testing',
        ]);

        // Create and authenticate user
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'organization_id' => $this->organization->id,
        ]);

        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function it_can_list_employees()
    {
        // Create some test employees
        Employee::create([
            'employee_id' => 'EMP2025001',
            'user_id' => $this->user->id,
            'organization_id' => $this->organization->id,
            'designation' => 'Software Developer',
            'department' => 'IT',
            'employment_type' => 'full_time',
            'joining_date' => now(),
            'status' => 'active',
            'basic_salary' => 50000,
            'gross_salary' => 60000,
        ]);

        $response = $this->getJson('/api/employees');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'employee_id',
                                'designation',
                                'department',
                                'employment_type',
                                'status',
                            ]
                        ]
                    ],
                    'message'
                ]);
    }

    /** @test */
    public function it_can_create_employee()
    {
        $employeeData = [
            'user_id' => $this->user->id,
            'organization_id' => $this->organization->id,
            'designation' => 'Software Developer',
            'department' => 'IT',
            'employment_type' => 'full_time',
            'joining_date' => now()->format('Y-m-d'),
            'basic_salary' => 50000,
            'gross_salary' => 60000,
            'pan_number' => 'ABCDE1234F',
            'aadhaar_number' => '123456789012',
        ];

        $response = $this->postJson('/api/employees', $employeeData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'employee_id',
                        'designation',
                        'department',
                        'employment_type',
                        'status',
                    ],
                    'message'
                ]);

        $this->assertDatabaseHas('employees', [
            'designation' => 'Software Developer',
            'department' => 'IT',
            'employment_type' => 'full_time',
        ]);
    }

    /** @test */
    public function it_can_create_employee_with_profile()
    {
        $employeeData = [
            'user_id' => $this->user->id,
            'organization_id' => $this->organization->id,
            'designation' => 'Software Developer',
            'department' => 'IT',
            'employment_type' => 'full_time',
            'joining_date' => now()->format('Y-m-d'),
            'basic_salary' => 50000,
            'gross_salary' => 60000,
            'profile' => [
                'date_of_birth' => '1990-01-01',
                'gender' => 'male',
                'marital_status' => 'single',
                'nationality' => 'Indian',
                'emergency_contact_name' => 'John Doe',
                'emergency_contact_phone' => '9876543210',
                'emergency_contact_relationship' => 'Father',
                'current_address' => '123 Test Street, Test City',
                'permanent_address' => '123 Test Street, Test City',
            ]
        ];

        $response = $this->postJson('/api/employees', $employeeData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('employees', [
            'designation' => 'Software Developer',
        ]);

        $this->assertDatabaseHas('employee_profiles', [
            'emergency_contact_name' => 'John Doe',
            'emergency_contact_phone' => '9876543210',
        ]);
    }

    /** @test */
    public function it_can_show_employee()
    {
        $employee = Employee::create([
            'employee_id' => 'EMP2025001',
            'user_id' => $this->user->id,
            'organization_id' => $this->organization->id,
            'designation' => 'Software Developer',
            'department' => 'IT',
            'employment_type' => 'full_time',
            'joining_date' => now(),
            'status' => 'active',
            'basic_salary' => 50000,
            'gross_salary' => 60000,
        ]);

        $response = $this->getJson("/api/employees/{$employee->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'employee_id',
                        'designation',
                        'department',
                        'employment_type',
                        'status',
                    ],
                    'message'
                ]);
    }

    /** @test */
    public function it_can_update_employee()
    {
        $employee = Employee::create([
            'employee_id' => 'EMP2025001',
            'user_id' => $this->user->id,
            'organization_id' => $this->organization->id,
            'designation' => 'Software Developer',
            'department' => 'IT',
            'employment_type' => 'full_time',
            'joining_date' => now(),
            'status' => 'active',
            'basic_salary' => 50000,
            'gross_salary' => 60000,
        ]);

        $updateData = [
            'designation' => 'Senior Software Developer',
            'basic_salary' => 60000,
            'gross_salary' => 72000,
        ];

        $response = $this->putJson("/api/employees/{$employee->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'designation' => 'Senior Software Developer',
                        'basic_salary' => '60000.00',
                        'gross_salary' => '72000.00',
                    ]
                ]);

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'designation' => 'Senior Software Developer',
            'basic_salary' => 60000,
            'gross_salary' => 72000,
        ]);
    }

    /** @test */
    public function it_can_delete_employee()
    {
        $employee = Employee::create([
            'employee_id' => 'EMP2025001',
            'user_id' => $this->user->id,
            'organization_id' => $this->organization->id,
            'designation' => 'Software Developer',
            'department' => 'IT',
            'employment_type' => 'full_time',
            'joining_date' => now(),
            'status' => 'active',
            'basic_salary' => 50000,
            'gross_salary' => 60000,
        ]);

        $response = $this->deleteJson("/api/employees/{$employee->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('employees', [
            'id' => $employee->id,
        ]);
    }

    /** @test */
    public function it_can_get_employee_statistics()
    {
        // Create some test employees
        Employee::create([
            'employee_id' => 'EMP2025001',
            'user_id' => $this->user->id,
            'organization_id' => $this->organization->id,
            'designation' => 'Software Developer',
            'department' => 'IT',
            'employment_type' => 'full_time',
            'joining_date' => now(),
            'status' => 'active',
            'basic_salary' => 50000,
            'gross_salary' => 60000,
        ]);

        Employee::create([
            'employee_id' => 'EMP2025002',
            'user_id' => $this->user->id,
            'organization_id' => $this->organization->id,
            'designation' => 'HR Manager',
            'department' => 'HR',
            'employment_type' => 'full_time',
            'joining_date' => now(),
            'status' => 'active',
            'basic_salary' => 70000,
            'gross_salary' => 84000,
        ]);

        $response = $this->getJson('/api/employees/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'total_employees',
                        'active_employees',
                        'by_department',
                        'by_employment_type',
                    ],
                    'message'
                ]);

        $responseData = $response->json('data');
        $this->assertEquals(2, $responseData['total_employees']);
        $this->assertEquals(2, $responseData['active_employees']);
        $this->assertArrayHasKey('IT', $responseData['by_department']);
        $this->assertArrayHasKey('HR', $responseData['by_department']);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $response = $this->postJson('/api/employees', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'user_id',
                    'organization_id',
                    'designation',
                    'department',
                    'employment_type',
                    'joining_date',
                ]);
    }

    /** @test */
    public function it_validates_employment_type_values()
    {
        $employeeData = [
            'user_id' => $this->user->id,
            'organization_id' => $this->organization->id,
            'designation' => 'Software Developer',
            'department' => 'IT',
            'employment_type' => 'invalid_type',
            'joining_date' => now()->format('Y-m-d'),
        ];

        $response = $this->postJson('/api/employees', $employeeData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['employment_type']);
    }

    /** @test */
    public function it_generates_unique_employee_id()
    {
        $employee1 = Employee::create([
            'employee_id' => 'EMP2025001',
            'user_id' => $this->user->id,
            'organization_id' => $this->organization->id,
            'designation' => 'Software Developer',
            'department' => 'IT',
            'employment_type' => 'full_time',
            'joining_date' => now(),
            'status' => 'active',
            'basic_salary' => 50000,
            'gross_salary' => 60000,
        ]);

        $employee2 = Employee::create([
            'employee_id' => 'EMP2025002',
            'user_id' => $this->user->id,
            'organization_id' => $this->organization->id,
            'designation' => 'HR Manager',
            'department' => 'HR',
            'employment_type' => 'full_time',
            'joining_date' => now(),
            'status' => 'active',
            'basic_salary' => 70000,
            'gross_salary' => 84000,
        ]);

        $this->assertNotEquals($employee1->employee_id, $employee2->employee_id);
        $this->assertStringStartsWith('EMP', $employee1->employee_id);
        $this->assertStringStartsWith('EMP', $employee2->employee_id);
    }
}
