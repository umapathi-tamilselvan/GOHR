# GOHR HR Management System - Development Guidelines

## 📋 Table of Contents
1. [Code Standards](#code-standards)
2. [Laravel Backend Guidelines](#laravel-backend-guidelines)
3. [Vue.js Frontend Guidelines](#vuejs-frontend-guidelines)
4. [Database Guidelines](#database-guidelines)
5. [API Development](#api-development)
6. [Testing Standards](#testing-standards)
7. [Security Guidelines](#security-guidelines)
8. [Performance Guidelines](#performance-guidelines)
9. [Documentation Standards](#documentation-standards)

## 🎯 Code Standards

### General Principles
- **Readability**: Code should be self-documenting and easy to understand
- **Consistency**: Follow established patterns and conventions
- **Maintainability**: Write code that's easy to modify and extend
- **Performance**: Optimize for performance without sacrificing readability
- **Security**: Security-first approach in all implementations

### Coding Conventions
- **PSR-12**: Follow PHP PSR-12 coding standards
- **ESLint + Prettier**: Use for frontend code formatting
- **TypeScript**: Implement for all frontend code
- **Git Hooks**: Pre-commit hooks for code quality checks

## 🐘 Laravel Backend Guidelines

### Directory Structure
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── API/           # API controllers
│   │   └── Web/           # Web controllers
│   ├── Requests/          # Form requests
│   ├── Resources/         # API resources
│   └── Middleware/        # Custom middleware
├── Models/                 # Eloquent models
├── Services/               # Business logic services
├── Repositories/           # Data access layer
├── Observers/              # Model observers
├── Events/                 # Event classes
├── Listeners/              # Event listeners
├── Jobs/                   # Queue jobs
├── Policies/               # Authorization policies
└── Providers/              # Service providers
```

### Model Guidelines

#### Basic Model Structure
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'designation',
        'department_id',
        'manager_id',
        'joining_date',
        'status'
    ];

    protected $casts = [
        'joining_date' => 'date',
        'status' => 'string'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    // Relationships
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    // Business Logic Methods
    public function isManager(): bool
    {
        return $this->subordinates()->exists();
    }

    public function getWorkingDays(): int
    {
        return $this->joining_date->diffInDays(now());
    }
}
```

#### Model Relationships
```php
// One-to-Many
public function attendances(): HasMany
{
    return $this->hasMany(Attendance::class);
}

// Many-to-Many
public function roles(): BelongsToMany
{
    return $this->belongsToMany(Role::class);
}

// One-to-One
public function profile(): HasOne
{
    return $this->hasOne(EmployeeProfile::class);
}

// Polymorphic
public function documents(): MorphMany
{
    return $this->morphMany(Document::class, 'documentable');
}
```

### Controller Guidelines

#### API Controller Structure
```php
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Services\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EmployeeController extends Controller
{
    public function __construct(
        private EmployeeService $employeeService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $employees = $this->employeeService->getEmployees($request->all());
        
        return EmployeeResource::collection($employees);
    }

    public function store(EmployeeRequest $request): JsonResponse
    {
        $employee = $this->employeeService->createEmployee($request->validated());
        
        return response()->json([
            'success' => true,
            'data' => new EmployeeResource($employee),
            'message' => 'Employee created successfully'
        ], 201);
    }

    public function show(Employee $employee): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new EmployeeResource($employee)
        ]);
    }

    public function update(EmployeeRequest $request, Employee $employee): JsonResponse
    {
        $employee = $this->employeeService->updateEmployee($employee, $request->validated());
        
        return response()->json([
            'success' => true,
            'data' => new EmployeeResource($employee),
            'message' => 'Employee updated successfully'
        ]);
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $this->employeeService->deleteEmployee($employee);
        
        return response()->json([
            'success' => true,
            'message' => 'Employee deleted successfully'
        ]);
    }
}
```

#### Form Request Validation
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage', Employee::class);
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee')?->id;
        
        return [
            'employee_id' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users')->ignore($employeeId)
            ],
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($employeeId)
            ],
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'designation' => 'required|string|max:100',
            'department_id' => 'required|exists:departments,id',
            'manager_id' => 'nullable|exists:users,id',
            'joining_date' => 'required|date|before_or_equal:today',
            'password' => $this->isMethod('POST') ? 'required|min:8' : 'nullable|min:8',
            
            // Profile data
            'profile.blood_group' => 'nullable|string|max:10',
            'profile.emergency_contact_name' => 'nullable|string|max:100',
            'profile.emergency_contact_phone' => 'nullable|string|max:20',
            'profile.current_address' => 'nullable|string|max:500',
            'profile.permanent_address' => 'nullable|string|max:500'
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.unique' => 'Employee ID already exists',
            'email.unique' => 'Email address already exists',
            'department_id.exists' => 'Selected department does not exist',
            'manager_id.exists' => 'Selected manager does not exist',
            'joining_date.before_or_equal' => 'Joining date cannot be in the future'
        ];
    }
}
```

### Service Layer Guidelines

#### Service Class Structure
```php
<?php

namespace App\Services;

use App\Models\Employee;
use App\Repositories\EmployeeRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class EmployeeService
{
    public function __construct(
        private EmployeeRepository $employeeRepository
    ) {}

    public function getEmployees(array $filters): LengthAwarePaginator
    {
        return $this->employeeRepository->getEmployees($filters);
    }

    public function createEmployee(array $data): Employee
    {
        // Business logic validation
        $this->validateEmployeeCreation($data);
        
        // Create employee
        $employee = $this->employeeRepository->create($data);
        
        // Create profile if provided
        if (isset($data['profile'])) {
            $employee->profile()->create($data['profile']);
        }
        
        // Send welcome email
        event(new EmployeeCreated($employee));
        
        return $employee;
    }

    public function updateEmployee(Employee $employee, array $data): Employee
    {
        // Business logic validation
        $this->validateEmployeeUpdate($employee, $data);
        
        // Update employee
        $employee = $this->employeeRepository->update($employee, $data);
        
        // Update profile if provided
        if (isset($data['profile'])) {
            $employee->profile()->updateOrCreate(
                ['employee_id' => $employee->id],
                $data['profile']
            );
        }
        
        return $employee;
    }

    public function deleteEmployee(Employee $employee): bool
    {
        // Check if employee can be deleted
        if ($employee->subordinates()->exists()) {
            throw new \Exception('Cannot delete employee with subordinates');
        }
        
        // Soft delete employee
        return $this->employeeRepository->delete($employee);
    }

    private function validateEmployeeCreation(array $data): void
    {
        // Additional business logic validation
        if (isset($data['manager_id'])) {
            $manager = Employee::find($data['manager_id']);
            if (!$manager || $manager->status !== 'active') {
                throw new \Exception('Invalid manager selected');
            }
        }
    }

    private function validateEmployeeUpdate(Employee $employee, array $data): void
    {
        // Additional business logic validation
        if (isset($data['manager_id']) && $data['manager_id'] == $employee->id) {
            throw new \Exception('Employee cannot be their own manager');
        }
    }
}
```

## 🟢 Vue.js Frontend Guidelines

### Component Structure

#### Single File Component
```vue
<template>
  <div class="employee-list">
    <!-- Search and Filters -->
    <div class="filters mb-6">
      <BaseInput
        v-model="searchQuery"
        placeholder="Search employees..."
        @input="handleSearch"
      />
      <BaseSelect
        v-model="selectedDepartment"
        :options="departmentOptions"
        placeholder="Select Department"
        @change="handleDepartmentChange"
      />
    </div>

    <!-- Employee Table -->
    <div class="table-container">
      <table class="min-w-full">
        <thead>
          <tr>
            <th @click="sortBy('name')" class="cursor-pointer">
              Name
              <SortIcon :direction="sortDirection" />
            </th>
            <th @click="sortBy('designation')" class="cursor-pointer">
              Designation
              <SortIcon :direction="sortDirection" />
            </th>
            <th>Department</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="employee in paginatedEmployees" :key="employee.id">
            <td>{{ employee.full_name }}</td>
            <td>{{ employee.designation }}</td>
            <td>{{ employee.department?.name }}</td>
            <td>
              <StatusBadge :status="employee.status" />
            </td>
            <td>
              <div class="flex space-x-2">
                <BaseButton
                  size="sm"
                  variant="outline"
                  @click="viewEmployee(employee)"
                >
                  View
                </BaseButton>
                <BaseButton
                  size="sm"
                  variant="outline"
                  @click="editEmployee(employee)"
                >
                  Edit
                </BaseButton>
                <BaseButton
                  size="sm"
                  variant="danger"
                  @click="deleteEmployee(employee)"
                >
                  Delete
                </BaseButton>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <Pagination
      :current-page="currentPage"
      :total-pages="totalPages"
      @page-change="handlePageChange"
    />

    <!-- Modals -->
    <EmployeeModal
      v-model="showEmployeeModal"
      :employee="selectedEmployee"
      @save="handleEmployeeSave"
    />
    
    <DeleteConfirmationModal
      v-model="showDeleteModal"
      :item-name="employeeToDelete?.full_name"
      @confirm="confirmDelete"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useEmployeeStore } from '@/stores/employee'
import { useNotification } from '@/composables/useNotification'
import type { Employee } from '@/types/employee'

// Types
interface EmployeeFilters {
  search?: string
  department?: string
  status?: string
}

// Composables
const employeeStore = useEmployeeStore()
const { showNotification } = useNotification()

// Reactive data
const searchQuery = ref('')
const selectedDepartment = ref('')
const selectedStatus = ref('')
const currentPage = ref(1)
const showEmployeeModal = ref(false)
const showDeleteModal = ref(false)
const selectedEmployee = ref<Employee | null>(null)
const employeeToDelete = ref<Employee | null>(null)

// Computed properties
const filteredEmployees = computed(() => {
  let employees = employeeStore.employees

  if (searchQuery.value) {
    employees = employees.filter(emp => 
      emp.full_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      emp.email.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  if (selectedDepartment.value) {
    employees = employees.filter(emp => emp.department?.id === selectedDepartment.value)
  }

  if (selectedStatus.value) {
    employees = employees.filter(emp => emp.status === selectedStatus.value)
  }

  return employees
})

const paginatedEmployees = computed(() => {
  const start = (currentPage.value - 1) * 20
  const end = start + 20
  return filteredEmployees.value.slice(start, end)
})

const totalPages = computed(() => 
  Math.ceil(filteredEmployees.value.length / 20)
)

const departmentOptions = computed(() => 
  employeeStore.departments.map(dept => ({
    value: dept.id,
    label: dept.name
  }))
)

// Methods
const handleSearch = () => {
  currentPage.value = 1
}

const handleDepartmentChange = () => {
  currentPage.value = 1
}

const sortBy = (field: keyof Employee) => {
  // Implement sorting logic
}

const handlePageChange = (page: number) => {
  currentPage.value = page
}

const viewEmployee = (employee: Employee) => {
  selectedEmployee.value = employee
  showEmployeeModal.value = true
}

const editEmployee = (employee: Employee) => {
  selectedEmployee.value = { ...employee }
  showEmployeeModal.value = true
}

const deleteEmployee = (employee: Employee) => {
  employeeToDelete.value = employee
  showDeleteModal.value = true
}

const confirmDelete = async () => {
  if (!employeeToDelete.value) return

  try {
    await employeeStore.deleteEmployee(employeeToDelete.value.id)
    showNotification('Employee deleted successfully', 'success')
    showDeleteModal.value = false
    employeeToDelete.value = null
  } catch (error) {
    showNotification('Failed to delete employee', 'error')
  }
}

const handleEmployeeSave = async (employeeData: Partial<Employee>) => {
  try {
    if (selectedEmployee.value?.id) {
      await employeeStore.updateEmployee(selectedEmployee.value.id, employeeData)
      showNotification('Employee updated successfully', 'success')
    } else {
      await employeeStore.createEmployee(employeeData)
      showNotification('Employee created successfully', 'success')
    }
    
    showEmployeeModal.value = false
    selectedEmployee.value = null
  } catch (error) {
    showNotification('Failed to save employee', 'error')
  }
}

// Lifecycle
onMounted(async () => {
  await employeeStore.fetchEmployees()
  await employeeStore.fetchDepartments()
})
</script>

<style scoped>
.employee-list {
  @apply p-6;
}

.filters {
  @apply flex gap-4 items-center;
}

.table-container {
  @apply bg-white rounded-lg shadow overflow-hidden;
}

table {
  @apply divide-y divide-gray-200;
}

th {
  @apply px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider;
}

td {
  @apply px-6 py-4 whitespace-nowrap text-sm text-gray-900;
}

.cursor-pointer {
  @apply cursor-pointer hover:bg-gray-50;
}
</style>
```

### Store Guidelines (Pinia)

#### Employee Store
```typescript
// stores/employee.ts
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { Employee, Department } from '@/types'
import { employeeApi } from '@/api/employee'

export const useEmployeeStore = defineStore('employee', () => {
  // State
  const employees = ref<Employee[]>([])
  const departments = ref<Department[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Getters
  const activeEmployees = computed(() => 
    employees.value.filter(emp => emp.status === 'active')
  )

  const employeesByDepartment = computed(() => {
    const grouped: Record<string, Employee[]> = {}
    employees.value.forEach(emp => {
      const deptName = emp.department?.name || 'Unknown'
      if (!grouped[deptName]) {
        grouped[deptName] = []
      }
      grouped[deptName].push(emp)
    })
    return grouped
  })

  // Actions
  const fetchEmployees = async () => {
    loading.value = true
    error.value = null
    
    try {
      const response = await employeeApi.getEmployees()
      employees.value = response.data
    } catch (err) {
      error.value = 'Failed to fetch employees'
      console.error('Error fetching employees:', err)
    } finally {
      loading.value = false
    }
  }

  const createEmployee = async (employeeData: Partial<Employee>) => {
    try {
      const response = await employeeApi.createEmployee(employeeData)
      employees.value.push(response.data)
      return response.data
    } catch (err) {
      throw new Error('Failed to create employee')
    }
  }

  const updateEmployee = async (id: number, employeeData: Partial<Employee>) => {
    try {
      const response = await employeeApi.updateEmployee(id, employeeData)
      const index = employees.value.findIndex(emp => emp.id === id)
      if (index !== -1) {
        employees.value[index] = response.data
      }
      return response.data
    } catch (err) {
      throw new Error('Failed to update employee')
    }
  }

  const deleteEmployee = async (id: number) => {
    try {
      await employeeApi.deleteEmployee(id)
      const index = employees.value.findIndex(emp => emp.id === id)
      if (index !== -1) {
        employees.value.splice(index, 1)
      }
    } catch (err) {
      throw new Error('Failed to delete employee')
    }
  }

  const fetchDepartments = async () => {
    try {
      const response = await employeeApi.getDepartments()
      departments.value = response.data
    } catch (err) {
      console.error('Error fetching departments:', err)
    }
  }

  return {
    // State
    employees,
    departments,
    loading,
    error,
    
    // Getters
    activeEmployees,
    employeesByDepartment,
    
    // Actions
    fetchEmployees,
    createEmployee,
    updateEmployee,
    deleteEmployee,
    fetchDepartments
  }
})
```

## 🗄️ Database Guidelines

### Migration Guidelines

#### Migration Structure
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 50)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 255)->unique();
            $table->string('phone', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->string('nationality', 100)->nullable();
            $table->string('designation', 100);
            $table->foreignId('department_id')->constrained()->onDelete('restrict');
            $table->foreignId('manager_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->date('joining_date');
            $table->enum('status', ['active', 'inactive', 'terminated'])->default('active');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['department_id', 'status']);
            $table->index(['manager_id', 'status']);
            $table->index('joining_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
```

#### Indexing Strategy
```php
// Primary indexes for performance
$table->index(['employee_id', 'date']); // Composite index for attendance
$table->index(['month', 'year', 'status']); // Composite index for payroll
$table->index(['employee_id', 'leave_type_id', 'year']); // Composite index for leave balance

// Full-text search indexes
$table->fullText(['first_name', 'last_name', 'email']);

// Partial indexes for specific conditions
$table->index(['status'])->where('status', 'active');
```

### Seeder Guidelines

#### Database Seeder
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\Shift;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create departments
        $departments = [
            ['name' => 'IT', 'code' => 'IT'],
            ['name' => 'Human Resources', 'code' => 'HR'],
            ['name' => 'Finance', 'code' => 'FIN'],
            ['name' => 'Marketing', 'code' => 'MKT'],
            ['name' => 'Operations', 'code' => 'OPS']
        ];

        foreach ($departments as $dept) {
            Department::create($dept);
        }

        // Create leave types
        $leaveTypes = [
            ['name' => 'Casual Leave', 'code' => 'CL', 'default_balance' => 12],
            ['name' => 'Sick Leave', 'code' => 'SL', 'default_balance' => 15],
            ['name' => 'Earned Leave', 'code' => 'EL', 'default_balance' => 30],
            ['name' => 'Maternity Leave', 'code' => 'ML', 'default_balance' => 180]
        ];

        foreach ($leaveTypes as $type) {
            LeaveType::create($type);
        }

        // Create shifts
        $shifts = [
            ['name' => 'General Shift', 'start_time' => '09:00', 'end_time' => '18:00'],
            ['name' => 'Night Shift', 'start_time' => '18:00', 'end_time' => '06:00', 'is_night_shift' => true]
        ];

        foreach ($shifts as $shift) {
            Shift::create($shift);
        }

        // Create sample employees
        $this->call([
            UserSeeder::class,
            LeaveBalanceSeeder::class,
            AttendanceSeeder::class
        ]);
    }
}
```

## 🔌 API Development

### API Resource Guidelines

#### Employee Resource
```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'gender' => $this->gender,
            'marital_status' => $this->marital_status,
            'nationality' => $this->nationality,
            'designation' => $this->designation,
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'manager' => new EmployeeResource($this->whenLoaded('manager')),
            'joining_date' => $this->joining_date->format('Y-m-d'),
            'status' => $this->status,
            'profile' => new EmployeeProfileResource($this->whenLoaded('profile')),
            'documents' => EmployeeDocumentResource::collection($this->whenLoaded('documents')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
```

### API Response Standards

#### Success Response
```php
return response()->json([
    'success' => true,
    'data' => $data,
    'message' => $message,
    'meta' => [
        'pagination' => [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage()
        ]
    ]
]);
```

#### Error Response
```php
return response()->json([
    'success' => false,
    'error' => [
        'code' => $errorCode,
        'message' => $errorMessage,
        'details' => $validationErrors ?? null
    ]
], $statusCode);
```

## 🧪 Testing Standards

### Test Coverage Requirements
- **Unit Tests**: 90%+ coverage for models and services
- **Feature Tests**: 100% coverage for all API endpoints
- **Integration Tests**: Complex workflow testing
- **Frontend Tests**: Component and store testing

### PHPUnit Test Example
```php
<?php

namespace Tests\Feature\API;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_get_employees_list(): void
    {
        Employee::factory()->count(5)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/employees');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'employee_id',
                        'first_name',
                        'last_name',
                        'email',
                        'designation'
                    ]
                ],
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page'
                ]
            ]);
    }

    public function test_can_create_employee(): void
    {
        $employeeData = [
            'employee_id' => 'EMP001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'designation' => 'Developer',
            'joining_date' => '2025-01-01'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/employees', $employeeData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Employee created successfully'
            ]);

        $this->assertDatabaseHas('users', [
            'employee_id' => 'EMP001',
            'email' => 'john.doe@example.com'
        ]);
    }

    public function test_cannot_create_employee_with_duplicate_email(): void
    {
        Employee::factory()->create(['email' => 'john@example.com']);

        $employeeData = [
            'employee_id' => 'EMP002',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'designation' => 'Developer',
            'joining_date' => '2025-01-01'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/employees', $employeeData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
```

## 🔐 Security Guidelines

### Authentication & Authorization
- **Laravel Sanctum**: Use for API authentication
- **Role-Based Access**: Implement with Spatie Laravel Permission
- **Policy-Based Authorization**: Use policies for model-level permissions
- **Input Validation**: Validate all inputs with form requests
- **CSRF Protection**: Enable for web routes

### Data Protection
- **Encryption**: Encrypt sensitive data at rest
- **Data Masking**: Mask PII in logs and exports
- **Access Logging**: Log all sensitive operations
- **Rate Limiting**: Implement API rate limiting
- **SQL Injection**: Use Eloquent ORM to prevent SQL injection

## ⚡ Performance Guidelines

### Database Optimization
- **Indexing**: Strategic index placement for common queries
- **Eager Loading**: Use with() to prevent N+1 queries
- **Query Optimization**: Optimize complex queries
- **Caching**: Implement Redis caching for frequently accessed data
- **Pagination**: Use pagination for large datasets

### Frontend Optimization
- **Lazy Loading**: Implement lazy loading for components
- **Code Splitting**: Use dynamic imports for route-based code splitting
- **Image Optimization**: Optimize images and use WebP format
- **Bundle Optimization**: Minimize bundle size with tree shaking
- **CDN Usage**: Use CDN for static assets

## 📚 Documentation Standards

### Code Documentation
- **PHPDoc**: Document all public methods and classes
- **Inline Comments**: Add comments for complex logic
- **README Files**: Maintain README for each module
- **API Documentation**: Keep API docs updated
- **Change Logs**: Document all significant changes

### Example PHPDoc
```php
/**
 * Create a new employee with profile and documents
 *
 * @param array $data Employee data including profile information
 * @return Employee Created employee instance
 * @throws \Exception When employee creation fails
 */
public function createEmployee(array $data): Employee
{
    // Implementation
}
```

---

## 📝 Document Information

**Document Version**: 1.0  
**Last Updated**: January 2025  
**Author**: GOHR Development Team  
**Review Cycle**: Quarterly  
**Next Review**: April 2025  

---

*These development guidelines ensure consistent, high-quality code across the GOHR HR Management System.* 