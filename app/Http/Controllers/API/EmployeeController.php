<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeProfile;
use App\Models\FamilyMember;
use App\Models\EmployeeDocument;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees with filters and pagination
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Employee::with(['user', 'organization', 'manager', 'profile'])
                ->when($request->department, function ($query, $department) {
                    return $query->where('department', $department);
                })
                ->when($request->location, function ($query, $location) {
                    return $query->where('location', $location);
                })
                ->when($request->status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->when($request->employment_type, function ($query, $type) {
                    return $query->where('employment_type', $type);
                })
                ->when($request->search, function ($query, $search) {
                    return $query->where(function ($q) use ($search) {
                        $q->where('employee_id', 'like', "%{$search}%")
                          ->orWhereHas('user', function ($userQuery) use ($search) {
                              $userQuery->where('name', 'like', "%{$search}%")
                                       ->orWhere('email', 'like', "%{$search}%");
                          });
                    });
                });

            $employees = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $employees,
                'message' => 'Employees retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving employees: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created employee
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id|unique:employees,user_id',
                'organization_id' => 'required|exists:organizations,id',
                'designation' => 'required|string|max:255',
                'department' => 'required|string|max:255',
                'location' => 'nullable|string|max:255',
                'employment_type' => ['required', Rule::in(['full_time', 'part_time', 'contract', 'intern'])],
                'joining_date' => 'required|date',
                'confirmation_date' => 'nullable|date|after:joining_date',
                'basic_salary' => 'nullable|numeric|min:0',
                'gross_salary' => 'nullable|numeric|min:0',
                'manager_id' => 'nullable|exists:employees,id',
                'bank_name' => 'nullable|string|max:255',
                'bank_account_number' => 'nullable|string|max:255',
                'ifsc_code' => 'nullable|string|max:255',
                'pan_number' => 'nullable|string|max:255',
                'aadhaar_number' => 'nullable|string|max:255',
                'pf_number' => 'nullable|string|max:255',
                'esi_number' => 'nullable|string|max:255',
                'uan_number' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Generate employee ID
            $employeeId = Employee::generateEmployeeId();
            
            // Create employee
            $employee = Employee::create(array_merge($request->all(), [
                'employee_id' => $employeeId,
                'status' => 'active'
            ]));

            // Create employee profile if profile data is provided
            if ($request->has('profile')) {
                $profileValidator = Validator::make($request->profile, [
                    'date_of_birth' => 'required|date|before:today',
                    'gender' => ['required', Rule::in(['male', 'female', 'other'])],
                    'marital_status' => ['required', Rule::in(['single', 'married', 'divorced', 'widowed'])],
                    'nationality' => 'nullable|string|max:255',
                    'blood_group' => 'nullable|string|max:10',
                    'emergency_contact_name' => 'required|string|max:255',
                    'emergency_contact_phone' => 'required|string|max:20',
                    'emergency_contact_relationship' => 'required|string|max:255',
                    'current_address' => 'required|string',
                    'permanent_address' => 'required|string',
                    'emergency_address' => 'nullable|string',
                    'emergency_contact_alternate' => 'nullable|string|max:255',
                    'emergency_contact_alternate_phone' => 'nullable|string|max:20',
                ]);

                if ($profileValidator->fails()) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Profile validation failed',
                        'errors' => $profileValidator->errors()
                    ], 422);
                }

                $employee->profile()->create($request->profile);
            }

            DB::commit();

            $employee->load(['user', 'organization', 'manager', 'profile']);

            return response()->json([
                'success' => true,
                'data' => $employee,
                'message' => 'Employee created successfully'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating employee: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified employee
     */
    public function show(Employee $employee): JsonResponse
    {
        try {
            $employee->load([
                'user', 
                'organization', 
                'manager', 
                'profile.familyMembers',
                'documents',
                'attendances' => function ($query) {
                    $query->latest()->limit(10);
                },
                'leaves' => function ($query) {
                    $query->latest()->limit(10);
                },
                'leaveBalances',
                'shiftAssignments.shift',
                'performanceReviews' => function ($query) {
                    $query->latest()->limit(5);
                }
            ]);

            return response()->json([
                'success' => true,
                'data' => $employee,
                'message' => 'Employee retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving employee: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified employee
     */
    public function update(Request $request, Employee $employee): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'designation' => 'sometimes|string|max:255',
                'department' => 'sometimes|string|max:255',
                'location' => 'nullable|string|max:255',
                'employment_type' => ['sometimes', Rule::in(['full_time', 'part_time', 'contract', 'intern'])],
                'confirmation_date' => 'nullable|date|after:joining_date',
                'resignation_date' => 'nullable|date|after:joining_date',
                'last_working_day' => 'nullable|date|after:resignation_date',
                'status' => ['sometimes', Rule::in(['active', 'inactive', 'terminated', 'resigned'])],
                'basic_salary' => 'nullable|numeric|min:0',
                'gross_salary' => 'nullable|numeric|min:0',
                'manager_id' => 'nullable|exists:employees,id',
                'bank_name' => 'nullable|string|max:255',
                'bank_account_number' => 'nullable|string|max:255',
                'ifsc_code' => 'nullable|string|max:255',
                'pan_number' => 'nullable|string|max:255',
                'aadhaar_number' => 'nullable|string|max:255',
                'pf_number' => 'nullable|string|max:255',
                'esi_number' => 'nullable|string|max:255',
                'uan_number' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $employee->update($request->all());

            $employee->load(['user', 'organization', 'manager', 'profile']);

            return response()->json([
                'success' => true,
                'data' => $employee,
                'message' => 'Employee updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating employee: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified employee
     */
    public function destroy(Employee $employee): JsonResponse
    {
        try {
            // Check if employee has active records
            if ($employee->attendances()->whereDate('created_at', today())->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete employee with active attendance records'
                ], 422);
            }

            $employee->delete();

            return response()->json([
                'success' => true,
                'message' => 'Employee deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting employee: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get employee statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_employees' => Employee::count(),
                'active_employees' => Employee::where('status', 'active')->count(),
                'inactive_employees' => Employee::where('status', 'inactive')->count(),
                'terminated_employees' => Employee::where('status', 'terminated')->count(),
                'resigned_employees' => Employee::where('status', 'resigned')->count(),
                'by_department' => Employee::getEmployeesByDepartment(),
                'by_location' => Employee::getEmployeesByLocation(),
                'by_employment_type' => Employee::selectRaw('employment_type, COUNT(*) as count')
                    ->groupBy('employment_type')
                    ->pluck('count', 'employment_type')
                    ->toArray(),
                'recent_joinings' => Employee::where('joining_date', '>=', now()->subMonths(3))
                    ->count(),
                'probation_employees' => Employee::whereNull('confirmation_date')
                    ->orWhere('confirmation_date', '>', now())
                    ->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Employee statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving employee statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get employee profile
     */
    public function profile(Employee $employee): JsonResponse
    {
        try {
            $employee->load(['profile.familyMembers']);

            return response()->json([
                'success' => true,
                'data' => $employee->profile,
                'message' => 'Employee profile retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving employee profile: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update employee profile
     */
    public function updateProfile(Request $request, Employee $employee): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'date_of_birth' => 'sometimes|date|before:today',
                'gender' => ['sometimes', Rule::in(['male', 'female', 'other'])],
                'marital_status' => ['sometimes', Rule::in(['single', 'married', 'divorced', 'widowed'])],
                'nationality' => 'nullable|string|max:255',
                'blood_group' => 'nullable|string|max:10',
                'emergency_contact_name' => 'sometimes|string|max:255',
                'emergency_contact_phone' => 'sometimes|string|max:20',
                'emergency_contact_relationship' => 'sometimes|string|max:255',
                'current_address' => 'sometimes|string',
                'permanent_address' => 'sometimes|string',
                'emergency_address' => 'nullable|string',
                'emergency_contact_alternate' => 'nullable|string|max:255',
                'emergency_contact_alternate_phone' => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            if ($employee->profile) {
                $employee->profile->update($request->all());
            } else {
                $employee->profile()->create($request->all());
            }

            $employee->load('profile.familyMembers');

            return response()->json([
                'success' => true,
                'data' => $employee->profile,
                'message' => 'Employee profile updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating employee profile: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get employee documents
     */
    public function documents(Employee $employee): JsonResponse
    {
        try {
            $documents = $employee->documents()->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $documents,
                'message' => 'Employee documents retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving employee documents: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload employee document
     */
    public function uploadDocument(Request $request, Employee $employee): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'document_type' => 'required|string|max:255',
                'document_name' => 'required|string|max:255',
                'document' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
                'issue_date' => 'nullable|date',
                'expiry_date' => 'nullable|date|after:issue_date',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('document');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('employee_documents', $fileName, 'public');

            $document = $employee->documents()->create([
                'document_type' => $request->document_type,
                'document_name' => $request->document_name,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'file_extension' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'issue_date' => $request->issue_date,
                'expiry_date' => $request->expiry_date,
                'description' => $request->description,
            ]);

            return response()->json([
                'success' => true,
                'data' => $document,
                'message' => 'Document uploaded successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error uploading document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get employee family members
     */
    public function familyMembers(Employee $employee): JsonResponse
    {
        try {
            if (!$employee->profile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee profile not found'
                ], 404);
            }

            $familyMembers = $employee->profile->familyMembers()->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $familyMembers,
                'message' => 'Family members retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving family members: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add family member
     */
    public function addFamilyMember(Request $request, Employee $employee): JsonResponse
    {
        try {
            if (!$employee->profile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee profile not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'relationship' => 'required|string|max:255',
                'date_of_birth' => 'nullable|date|before:today',
                'occupation' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'is_dependent' => 'boolean',
                'address' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $familyMember = $employee->profile->familyMembers()->create($request->all());

            return response()->json([
                'success' => true,
                'data' => $familyMember,
                'message' => 'Family member added successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding family member: ' . $e->getMessage()
            ], 500);
        }
    }
} 