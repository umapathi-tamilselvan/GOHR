# GOHR - Development Guidelines

## Table of Contents
1. [Code Standards & Conventions](#code-standards--conventions)
2. [Module Development Patterns](#module-development-patterns)
3. [Database Design Guidelines](#database-design-guidelines)
4. [Frontend Development Standards](#frontend-development-standards)
5. [Testing Guidelines](#testing-guidelines)
6. [Security Guidelines](#security-guidelines)
7. [Performance Guidelines](#performance-guidelines)
8. [Documentation Standards](#documentation-standards)
9. [Deployment Guidelines](#deployment-guidelines)

---

## Code Standards & Conventions

### PHP/Laravel Standards

#### PSR-12 Compliance
```php
// ✅ Correct formatting
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::with(['organization', 'roles'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->paginate(15);

        return view('users.index', compact('users'));
    }
}
```

#### Naming Conventions
```php
// Controllers: PascalCase, singular
UserController, LeaveController, PayrollController

// Models: PascalCase, singular
User, Leave, Payroll, LeaveType

// Methods: camelCase
getUserData(), calculatePayroll(), validateLeaveRequest()

// Variables: camelCase
$userData, $leaveBalance, $payrollAmount

// Constants: UPPER_SNAKE_CASE
const MAX_LEAVE_DAYS = 30;
const DEFAULT_SALARY = 50000;
```

#### File Organization
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   ├── UserController.php
│   │   ├── LeaveController.php
│   │   └── PayrollController.php
│   ├── Requests/
│   │   ├── Auth/
│   │   ├── UserRequest.php
│   │   ├── LeaveRequest.php
│   │   └── PayrollRequest.php
│   └── Resources/
│       ├── UserResource.php
│       ├── LeaveResource.php
│       └── PayrollResource.php
├── Models/
│   ├── User.php
│   ├── Leave.php
│   ├── LeaveType.php
│   └── Payroll.php
├── Policies/
│   ├── UserPolicy.php
│   ├── LeavePolicy.php
│   └── PayrollPolicy.php
└── Observers/
    ├── UserObserver.php
    ├── LeaveObserver.php
    └── PayrollObserver.php
```

### Database Conventions

#### Table Naming
```sql
-- Tables: plural, snake_case
users, leave_types, leave_balances, payrolls

-- Pivot tables: singular_model1_singular_model2
project_members, user_roles

-- Foreign keys: singular_table_id
user_id, organization_id, leave_type_id
```

#### Column Naming
```sql
-- Primary keys: id
-- Foreign keys: singular_table_id
-- Timestamps: created_at, updated_at
-- Soft deletes: deleted_at
-- Status fields: status (ENUM)
-- Boolean fields: is_active, has_permission
-- Date fields: start_date, end_date, hire_date
-- Amount fields: amount, salary, balance (DECIMAL)
```

---

## Module Development Patterns

### Standard Controller Pattern

#### Base Controller Structure
```php
<?php

namespace App\Http\Controllers;

use App\Models\{ModelName};
use App\Http\Requests\{ModelName}Request;
use App\Http\Resources\{ModelName}Resource;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class {ModelName}Controller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', {ModelName}::class);
        
        $items = {ModelName}::with(['relationships'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('{module-name}.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', {ModelName}::class);
        
        return view('{module-name}.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store({ModelName}Request $request): RedirectResponse
    {
        $this->authorize('create', {ModelName}::class);
        
        $item = {ModelName}::create($request->validated());
        
        // Log activity
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => '{module-name}_created',
            'auditable_id' => $item->id,
            'auditable_type' => {ModelName}::class,
            'new_values' => $item->toArray()
        ]);
        
        return redirect()
            ->route('{module-name}.index')
            ->with('success', '{ModelName} created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show({ModelName} $item): View
    {
        $this->authorize('view', $item);
        
        return view('{module-name}.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit({ModelName} $item): View
    {
        $this->authorize('update', $item);
        
        return view('{module-name}.edit', compact('item'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update({ModelName}Request $request, {ModelName} $item): RedirectResponse
    {
        $this->authorize('update', $item);
        
        $oldValues = $item->toArray();
        $item->update($request->validated());
        
        // Log activity
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => '{module-name}_updated',
            'auditable_id' => $item->id,
            'auditable_type' => {ModelName}::class,
            'old_values' => $oldValues,
            'new_values' => $item->getChanges()
        ]);
        
        return redirect()
            ->route('{module-name}.index')
            ->with('success', '{ModelName} updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy({ModelName} $item): RedirectResponse
    {
        $this->authorize('delete', $item);
        
        $oldValues = $item->toArray();
        $item->delete();
        
        // Log activity
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => '{module-name}_deleted',
            'auditable_id' => $item->id,
            'auditable_type' => {ModelName}::class,
            'old_values' => $oldValues
        ]);
        
        return redirect()
            ->route('{module-name}.index')
            ->with('success', '{ModelName} deleted successfully.');
    }
}
```

### Standard Model Pattern

#### Base Model Structure
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class {ModelName} extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'status',
        'user_id',
        'organization_id',
        // Add other fillable fields
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Boot the model and add global scopes.
     */
    protected static function booted(): void
    {
        // Organization scope for multi-tenancy
        static::addGlobalScope('organization', function (Builder $query) {
            if (!auth()->user()?->hasRole('Super Admin')) {
                $query->where('organization_id', auth()->user()?->organization_id);
            }
        });
    }

    /**
     * Get the user that owns the model.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the organization that owns the model.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Scope a query to only include active records.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }

    /**
     * Scope a query to only include records for a specific user.
     */
    public function scopeForUser(Builder $query, User $user): void
    {
        $query->where('user_id', $user->id);
    }

    /**
     * Get the status badge HTML.
     */
    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'active' => 'badge-success',
            'pending' => 'badge-warning',
            'completed' => 'badge-info',
            'cancelled' => 'badge-danger',
        ];

        $class = $badges[$this->status] ?? 'badge-secondary';
        
        return "<span class='badge {$class}'>{$this->status}</span>";
    }
}
```

### Standard Request Validation Pattern

#### Form Request Structure
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class {ModelName}Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'string', Rule::in(['active', 'pending', 'completed', 'cancelled'])],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'amount' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'user_id' => ['required', 'exists:users,id'],
            'organization_id' => ['required', 'exists:organizations,id'],
        ];

        // Add unique validation for update
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['name'][] = Rule::unique('{table_name}')->ignore($this->route('{module-name}'));
        } else {
            $rules['name'][] = Rule::unique('{table_name}');
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.unique' => 'This name is already taken.',
            'start_date.after_or_equal' => 'The start date must be today or a future date.',
            'end_date.after' => 'The end date must be after the start date.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'name',
            'description' => 'description',
            'start_date' => 'start date',
            'end_date' => 'end date',
            'amount' => 'amount',
        ];
    }
}
```

---

## Database Design Guidelines

### Migration Standards

#### Standard Migration Structure
```php
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
        Schema::create('{table_name}', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            
            // Basic fields
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'pending', 'completed', 'cancelled'])->default('pending');
            
            // Date fields
            $table->date('start_date');
            $table->date('end_date')->nullable();
            
            // Amount fields
            $table->decimal('amount', 10, 2)->nullable();
            
            // Boolean fields
            $table->boolean('is_active')->default(true);
            
            // JSON fields
            $table->json('metadata')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['organization_id', 'status']);
            $table->index('start_date');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('{table_name}');
    }
};
```

### Seeder Standards

#### Standard Seeder Structure
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{ModelName};
use App\Models\User;
use App\Models\Organization;

class {ModelName}Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organizations = Organization::all();
        $users = User::all();

        foreach ($organizations as $organization) {
            $organizationUsers = $users->where('organization_id', $organization->id);
            
            foreach ($organizationUsers as $user) {
                {ModelName}::factory()
                    ->count(rand(3, 8))
                    ->create([
                        'user_id' => $user->id,
                        'organization_id' => $organization->id,
                    ]);
            }
        }
    }
}
```

---

## Frontend Development Standards

### Blade Template Standards

#### Standard Layout Structure
```php
{{-- resources/views/{module-name}/index.blade.php --}}
@extends('layouts.app')

@section('title', '{Module Name} Management')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">{Module Name} Management</h1>
                @can('create', App\Models\{ModelName}::class)
                    <a href="{{ route('{module-name}.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add New {ModelName}
                    </a>
                @endcan
            </div>
        </div>
    </div>

    {{-- Search and Filters --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('{module-name}.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control form-control-sm" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Search by name...">
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select form-select-sm" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                <a href="{{ route('{module-name}.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($items->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Amount</th>
                                        <th>Created By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $item)
                                        <tr>
                                            <td>{{ $item->name }}</td>
                                            <td>{!! $item->status_badge !!}</td>
                                            <td>{{ $item->start_date->format('M d, Y') }}</td>
                                            <td>{{ $item->end_date?->format('M d, Y') ?? '-' }}</td>
                                            <td>{{ $item->amount ? '$' . number_format($item->amount, 2) : '-' }}</td>
                                            <td>{{ $item->user->name }}</td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    @can('view', $item)
                                                        <a href="{{ route('{module-name}.show', $item) }}" 
                                                           class="btn btn-info" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    @endcan
                                                    @can('update', $item)
                                                        <a href="{{ route('{module-name}.edit', $item) }}" 
                                                           class="btn btn-warning" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endcan
                                                    @can('delete', $item)
                                                        <button type="button" class="btn btn-danger" title="Delete"
                                                                onclick="deleteItem({{ $item->id }})">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        {{-- Pagination --}}
                        <div class="d-flex justify-content-center">
                            {{ $items->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No {module-name} found</h5>
                            <p class="text-muted">No {module-name} match your search criteria.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
@can('delete', App\Models\{ModelName}::class)
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this {module-name}? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif

@endsection

@push('scripts')
<script>
function deleteItem(id) {
    if (confirm('Are you sure you want to delete this {module-name}?')) {
        document.getElementById('deleteForm').action = `/{{ config('app.locale') }}/{module-name}/${id}`;
        document.getElementById('deleteForm').submit();
    }
}
</script>
@endpush
```

### Form Standards

#### Standard Form Structure
```php
{{-- resources/views/{module-name}/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Create {ModelName}')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Create New {ModelName}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('{module-name}.store') }}" method="POST" class="needs-validation" novalidate>
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="">Select Status</option>
                                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                           id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                           id="end_date" name="end_date" value="{{ old('end_date') }}">
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                               id="amount" name="amount" value="{{ old('amount') }}" 
                                               step="0.01" min="0" max="999999.99">
                                    </div>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="user_id" class="form-label">User <span class="text-danger">*</span></label>
                                    <select class="form-select @error('user_id') is-invalid @enderror" 
                                            id="user_id" name="user_id" required>
                                        <option value="">Select User</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('{module-name}.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create {ModelName}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();
</script>
@endpush
```

---

## Testing Guidelines

### Test Structure Standards

#### Feature Test Pattern
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\{ModelName};
use App\Models\User;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class {ModelName}Test extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id
        ]);
    }

    /** @test */
    public function user_can_view_{module-name}_list()
    {
        $this->actingAs($this->user);
        
        {ModelName}::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'organization_id' => $this->organization->id
        ]);
        
        $response = $this->get(route('{module-name}.index'));
        
        $response->assertStatus(200);
        $response->assertViewIs('{module-name}.index');
        $response->assertViewHas('items');
    }

    /** @test */
    public function user_can_create_new_{module-name}()
    {
        $this->actingAs($this->user);
        
        $data = [
            'name' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'status' => 'active',
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(30)->format('Y-m-d'),
            'amount' => $this->faker->randomFloat(2, 100, 10000),
            'user_id' => $this->user->id,
            'organization_id' => $this->organization->id,
        ];
        
        $response = $this->post(route('{module-name}.store'), $data);
        
        $response->assertRedirect(route('{module-name}.index'));
        $this->assertDatabaseHas('{table_name}', [
            'name' => $data['name'],
            'user_id' => $this->user->id
        ]);
    }

    /** @test */
    public function user_can_update_{module-name}()
    {
        $this->actingAs($this->user);
        
        ${module-name} = {ModelName}::factory()->create([
            'user_id' => $this->user->id,
            'organization_id' => $this->organization->id
        ]);
        
        $updatedData = [
            'name' => 'Updated Name',
            'status' => 'completed',
            'user_id' => $this->user->id,
            'organization_id' => $this->organization->id,
        ];
        
        $response = $this->put(route('{module-name}.update', ${module-name}), $updatedData);
        
        $response->assertRedirect(route('{module-name}.index'));
        $this->assertDatabaseHas('{table_name}', [
            'id' => ${module-name}->id,
            'name' => 'Updated Name',
            'status' => 'completed'
        ]);
    }

    /** @test */
    public function user_can_delete_{module-name}()
    {
        $this->actingAs($this->user);
        
        ${module-name} = {ModelName}::factory()->create([
            'user_id' => $this->user->id,
            'organization_id' => $this->organization->id
        ]);
        
        $response = $this->delete(route('{module-name}.destroy', ${module-name}));
        
        $response->assertRedirect(route('{module-name}.index'));
        $this->assertSoftDeleted('{table_name}', ['id' => ${module-name}->id]);
    }

    /** @test */
    public function validation_prevents_invalid_data()
    {
        $this->actingAs($this->user);
        
        $response = $this->post(route('{module-name}.store'), []);
        
        $response->assertSessionHasErrors(['name', 'status', 'start_date', 'user_id']);
    }
}
```

#### Unit Test Pattern
```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\{ModelName};
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class {ModelName}ModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_{module-name}()
    {
        $user = User::factory()->create();
        
        ${module-name} = {ModelName}::create([
            'name' => 'Test {ModelName}',
            'status' => 'active',
            'user_id' => $user->id,
            'organization_id' => $user->organization_id,
        ]);
        
        $this->assertDatabaseHas('{table_name}', [
            'name' => 'Test {ModelName}',
            'status' => 'active'
        ]);
    }

    /** @test */
    public function it_has_required_relationships()
    {
        $user = User::factory()->create();
        ${module-name} = {ModelName}::factory()->create([
            'user_id' => $user->id,
            'organization_id' => $user->organization_id,
        ]);
        
        $this->assertInstanceOf(User::class, ${module-name}->user);
        $this->assertEquals($user->id, ${module-name}->user->id);
    }

    /** @test */
    public function it_can_scope_by_status()
    {
        $user = User::factory()->create();
        
        {ModelName}::factory()->create([
            'status' => 'active',
            'user_id' => $user->id,
            'organization_id' => $user->organization_id,
        ]);
        
        {ModelName}::factory()->create([
            'status' => 'pending',
            'user_id' => $user->id,
            'organization_id' => $user->organization_id,
        ]);
        
        $activeItems = {ModelName}::active()->get();
        
        $this->assertEquals(1, $activeItems->count());
        $this->assertEquals('active', $activeItems->first()->status);
    }
}
```

---

## Security Guidelines

### Authentication & Authorization

#### Policy Standards
```php
<?php

namespace App\Policies;

use App\Models\{ModelName};
use App\Models\User;

class {ModelName}Policy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'HR', 'Manager']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, {ModelName} ${module-name}): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }
        
        return $user->organization_id === ${module-name}->organization_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'HR', 'Manager']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, {ModelName} ${module-name}): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }
        
        return $user->organization_id === ${module-name}->organization_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, {ModelName} ${module-name}): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }
        
        return $user->organization_id === ${module-name}->organization_id;
    }
}
```

### Input Validation & Sanitization

#### Validation Rules
```php
// Standard validation rules for different field types
$validationRules = [
    // Text fields
    'name' => ['required', 'string', 'max:255'],
    'description' => ['nullable', 'string', 'max:1000'],
    
    // Email fields
    'email' => ['required', 'email', 'unique:users,email'],
    
    // Date fields
    'start_date' => ['required', 'date', 'after_or_equal:today'],
    'end_date' => ['required', 'date', 'after:start_date'],
    
    // Numeric fields
    'amount' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
    'quantity' => ['required', 'integer', 'min:1', 'max:999999'],
    
    // Enum fields
    'status' => ['required', 'string', Rule::in(['active', 'pending', 'completed', 'cancelled'])],
    
    // Foreign key fields
    'user_id' => ['required', 'exists:users,id'],
    'organization_id' => ['required', 'exists:organizations,id'],
    
    // File uploads
    'document' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'], // 10MB max
    
    // Arrays
    'tags' => ['nullable', 'array'],
    'tags.*' => ['string', 'max:50'],
];
```

---

## Performance Guidelines

### Database Optimization

#### Query Optimization
```php
// Use eager loading to prevent N+1 queries
$items = {ModelName}::with(['user', 'organization'])
    ->where('status', 'active')
    ->get();

// Use pagination for large datasets
$items = {ModelName}::with(['user'])
    ->paginate(15);

// Use database indexes
Schema::table('{table_name}', function (Blueprint $table) {
    $table->index(['user_id', 'status']);
    $table->index(['organization_id', 'created_at']);
});

// Use chunking for large operations
{ModelName}::chunk(1000, function ($items) {
    foreach ($items as $item) {
        // Process item
    }
});
```

#### Caching Strategy
```php
// Cache frequently accessed data
public function getDashboardStats(): array
{
    return Cache::remember('dashboard_stats', 300, function () {
        return [
            'total_items' => {ModelName}::count(),
            'active_items' => {ModelName}::where('status', 'active')->count(),
            'recent_items' => {ModelName}::latest()->take(5)->get(),
        ];
    });
}

// Cache user permissions
public function getUserPermissions(User $user): array
{
    return Cache::remember("user_permissions_{$user->id}", 3600, function () use ($user) {
        return $user->getAllPermissions()->pluck('name')->toArray();
    });
}
```

---

## Documentation Standards

### Code Documentation

#### PHPDoc Standards
```php
/**
 * User model for authentication and authorization.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property int $organization_id
 * @property \Carbon\Carbon|null $email_verified_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read \App\Models\Organization $organization
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Attendance[] $attendances
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Role[] $roles
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Permission[] $permissions
 */
class User extends Authenticatable
{
    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * Scope a query to only include active users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }
}
```

### README Standards

#### Module README Template
```markdown
# {Module Name} Module

## Overview
Brief description of the module and its purpose.

## Features
- Feature 1
- Feature 2
- Feature 3

## Database Schema
```sql
{table_name}:
- id (Primary Key)
- name (VARCHAR 255)
- description (TEXT NULL)
- status (ENUM)
- user_id (Foreign Key)
- organization_id (Foreign Key)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

## API Endpoints
- `GET /{module-name}` - List all {module-name}
- `POST /{module-name}` - Create new {module-name}
- `GET /{module-name}/{id}` - Get specific {module-name}
- `PUT /{module-name}/{id}` - Update {module-name}
- `DELETE /{module-name}/{id}` - Delete {module-name}

## Access Control
- **Super Admin**: Full access
- **HR**: Manage within organization
- **Manager**: Manage assigned items
- **Employee**: View own items

## Usage Examples
```php
// Create new {module-name}
${module-name} = {ModelName}::create([
    'name' => 'Example',
    'status' => 'active',
    'user_id' => $user->id,
    'organization_id' => $user->organization_id,
]);

// Get active {module-name}
$activeItems = {ModelName}::active()->get();
```
```

---

## Deployment Guidelines

### Environment Configuration

#### Production Environment
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=your-database
DB_USERNAME=your-username
DB_PASSWORD=your-password

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

#### Deployment Checklist
- [ ] Run all tests: `php artisan test`
- [ ] Check code quality: `./vendor/bin/pint`
- [ ] Optimize for production: `php artisan config:cache`
- [ ] Update dependencies: `composer install --optimize-autoloader --no-dev`
- [ ] Set proper file permissions
- [ ] Configure database backups
- [ ] Set up monitoring and logging
- [ ] Configure SSL certificate
- [ ] Set up rate limiting
- [ ] Configure firewall rules

---

**Document Version**: 1.0  
**Last Updated**: January 2025  
**Next Review**: February 2025  
**Status**: Active Development 