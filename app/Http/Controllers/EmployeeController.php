<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeRequest;
use App\Models\Employee;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the employees.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Employee::class);

        $query = Employee::with(['user', 'manager', 'organization', 'profile'])
            ->when(auth()->user()->hasRole('hr'), function ($query) {
                $query->forOrganization(auth()->user()->organization_id);
            })
            ->when(auth()->user()->hasRole('manager'), function ($query) {
                $query->where(function ($q) {
                    $q->where('manager_id', auth()->id())
                      ->orWhere('user_id', auth()->id());
                });
            })
            ->when($request->search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhere('employee_id', 'like', "%{$search}%")
                ->orWhere('department', 'like', "%{$search}%")
                ->orWhere('position', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->byStatus($status);
            })
            ->when($request->department, function ($query, $department) {
                $query->byDepartment($department);
            })
            ->when($request->manager_id, function ($query, $managerId) {
                $query->where('manager_id', $managerId);
            });

        $employees = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get filter options
        $departments = Employee::distinct()->pluck('department')->filter();
        $managers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['manager', 'hr']);
        })->get();

        return view('employees.index', compact('employees', 'departments', 'managers'));
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create(): View
    {
        $this->authorize('create', Employee::class);

        $users = User::whereDoesntHave('employee')
            ->when(auth()->user()->hasRole('hr'), function ($query) {
                $query->where('organization_id', auth()->user()->organization_id);
            })
            ->get();

        $organizations = Organization::when(auth()->user()->hasRole('hr'), function ($query) {
            $query->where('id', auth()->user()->organization_id);
        })->get();

        $managers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['manager', 'hr']);
        })
        ->when(auth()->user()->hasRole('hr'), function ($query) {
            $query->where('organization_id', auth()->user()->organization_id);
        })
        ->get();

        return view('employees.create', compact('users', 'organizations', 'managers'));
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(EmployeeRequest $request): RedirectResponse
    {
        $this->authorize('create', Employee::class);

        try {
            DB::beginTransaction();

            $employee = Employee::create($request->validated());

            DB::commit();

            return redirect()->route('employees.show', $employee)
                ->with('success', 'Employee created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create employee. ' . $e->getMessage());
        }
    }

    /**
     * Display the specified employee.
     */
    public function show(Employee $employee): View
    {
        $this->authorize('view', $employee);

        $employee->load(['user', 'manager', 'organization', 'profile', 'documents.uploadedBy']);

        // Get related data
        $recentAttendances = $employee->attendances()
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();

        $recentLeaves = $employee->leaves()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $leaveBalances = $employee->leaveBalances()
            ->with('leaveType')
            ->get();

        return view('employees.show', compact(
            'employee',
            'recentAttendances',
            'recentLeaves',
            'leaveBalances'
        ));
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit(Employee $employee): View
    {
        $this->authorize('update', $employee);

        $employee->load(['user', 'manager', 'organization', 'profile']);

        $organizations = Organization::when(auth()->user()->hasRole('hr'), function ($query) {
            $query->where('id', auth()->user()->organization_id);
        })->get();

        $managers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['manager', 'hr']);
        })
        ->when(auth()->user()->hasRole('hr'), function ($query) {
            $query->where('organization_id', auth()->user()->organization_id);
        })
        ->get();

        return view('employees.edit', compact('employee', 'organizations', 'managers'));
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(EmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $this->authorize('update', $employee);

        try {
            DB::beginTransaction();

            $employee->update($request->validated());

            DB::commit();

            return redirect()->route('employees.show', $employee)
                ->with('success', 'Employee updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update employee. ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified employee from storage.
     */
    public function destroy(Employee $employee): RedirectResponse
    {
        $this->authorize('delete', $employee);

        try {
            DB::beginTransaction();

            // Delete associated documents
            foreach ($employee->documents as $document) {
                if (Storage::exists($document->file_path)) {
                    Storage::delete($document->file_path);
                }
            }

            $employee->delete();

            DB::commit();

            return redirect()->route('employees.index')
                ->with('success', 'Employee deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete employee. ' . $e->getMessage());
        }
    }

    /**
     * Display employee directory.
     */
    public function directory(Request $request): View
    {
        $this->authorize('viewDirectory', Employee::class);

        $query = Employee::with(['user', 'manager', 'organization'])
            ->active()
            ->when(auth()->user()->hasRole('hr'), function ($query) {
                $query->forOrganization(auth()->user()->organization_id);
            })
            ->when(auth()->user()->hasRole('manager'), function ($query) {
                $query->where(function ($q) {
                    $q->where('manager_id', auth()->id())
                      ->orWhere('user_id', auth()->id());
                });
            })
            ->when($request->search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhere('department', 'like', "%{$search}%")
                ->orWhere('position', 'like', "%{$search}%");
            })
            ->when($request->department, function ($query, $department) {
                $query->byDepartment($department);
            });

        $employees = $query->orderBy('user.name')->paginate(20);

        $departments = Employee::distinct()->pluck('department')->filter();

        return view('employees.directory', compact('employees', 'departments'));
    }

    /**
     * Display employee onboarding page.
     */
    public function onboarding(): View
    {
        $this->authorize('manageOnboarding', Employee::class);

        $pendingEmployees = Employee::with(['user'])
            ->where('status', 'probation')
            ->when(auth()->user()->hasRole('hr'), function ($query) {
                $query->forOrganization(auth()->user()->organization_id);
            })
            ->orderBy('hire_date', 'desc')
            ->get();

        $recentHires = Employee::with(['user'])
            ->where('status', 'active')
            ->when(auth()->user()->hasRole('hr'), function ($query) {
                $query->forOrganization(auth()->user()->organization_id);
            })
            ->where('hire_date', '>=', now()->subDays(30))
            ->orderBy('hire_date', 'desc')
            ->get();

        return view('employees.onboarding', compact('pendingEmployees', 'recentHires'));
    }

    /**
     * Upload document for employee.
     */
    public function uploadDocument(Request $request, Employee $employee): JsonResponse
    {
        $this->authorize('uploadDocuments', $employee);

        $request->validate([
            'document_type' => 'required|in:contract,certificate,id_proof,resume,other',
            'title' => 'required|string|max:255',
            'document' => 'required|file|max:10240', // 10MB max
            'expiry_date' => 'nullable|date|after:today',
        ]);

        try {
            $file = $request->file('document');
            $path = $file->store('employee-documents', 'private');

            $employee->documents()->create([
                'document_type' => $request->document_type,
                'title' => $request->title,
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'uploaded_by' => auth()->id(),
                'expiry_date' => $request->expiry_date,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete document for employee.
     */
    public function deleteDocument(Employee $employee, $documentId): JsonResponse
    {
        $this->authorize('deleteDocuments', $employee);

        $document = $employee->documents()->findOrFail($documentId);

        try {
            if (Storage::exists($document->file_path)) {
                Storage::delete($document->file_path);
            }

            $document->delete();

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display employee reports.
     */
    public function report(Request $request): View
    {
        $this->authorize('viewReports', Employee::class);

        $organizationId = auth()->user()->hasRole('hr') 
            ? auth()->user()->organization_id 
            : null;

        // Employee statistics
        $totalEmployees = Employee::when($organizationId, function ($query) use ($organizationId) {
            $query->forOrganization($organizationId);
        })->count();

        $activeEmployees = Employee::when($organizationId, function ($query) use ($organizationId) {
            $query->forOrganization($organizationId);
        })->active()->count();

        $probationEmployees = Employee::when($organizationId, function ($query) use ($organizationId) {
            $query->forOrganization($organizationId);
        })->byStatus('probation')->count();

        $terminatedEmployees = Employee::when($organizationId, function ($query) use ($organizationId) {
            $query->forOrganization($organizationId);
        })->byStatus('terminated')->count();

        // Department statistics
        $departmentStats = Employee::when($organizationId, function ($query) use ($organizationId) {
            $query->forOrganization($organizationId);
        })
        ->select('department', DB::raw('count(*) as count'))
        ->groupBy('department')
        ->orderBy('count', 'desc')
        ->get();

        // Recent hires
        $recentHires = Employee::with(['user'])
            ->when($organizationId, function ($query) use ($organizationId) {
                $query->forOrganization($organizationId);
            })
            ->where('hire_date', '>=', now()->subMonths(6))
            ->orderBy('hire_date', 'desc')
            ->get();

        // Documents expiring soon
        $expiringDocuments = $employee->documents()
            ->expiringSoon(30)
            ->with(['employee.user'])
            ->get();

        return view('employees.report', compact(
            'totalEmployees',
            'activeEmployees',
            'probationEmployees',
            'terminatedEmployees',
            'departmentStats',
            'recentHires',
            'expiringDocuments'
        ));
    }
} 