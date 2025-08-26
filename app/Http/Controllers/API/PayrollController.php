<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\Employee;
use App\Models\SalaryComponent;
use App\Models\SalaryDeduction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class PayrollController extends Controller
{
    /**
     * Display a listing of payrolls with filters and pagination
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Payroll::with(['employee.user', 'approvedBy'])
                ->when($request->month, function ($query, $month) {
                    return $query->where('month', $month);
                })
                ->when($request->year, function ($query, $year) {
                    return $query->where('year', $year);
                })
                ->when($request->status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->when($request->employee_id, function ($query, $employeeId) {
                    return $query->where('employee_id', $employeeId);
                })
                ->when($request->search, function ($query, $search) {
                    return $query->whereHas('employee.user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                                 ->orWhere('email', 'like', "%{$search}%");
                    });
                });

            $payrolls = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $payrolls,
                'message' => 'Payrolls retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving payrolls: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created payroll
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|exists:employees,id',
                'month' => 'required|integer|between:1,12',
                'year' => 'required|integer|min:2020',
                'basic_salary' => 'required|numeric|min:0',
                'gross_salary' => 'required|numeric|min:0',
                'tds_amount' => 'nullable|numeric|min:0',
                'pf_amount' => 'nullable|numeric|min:0',
                'esi_amount' => 'nullable|numeric|min:0',
                'professional_tax' => 'nullable|numeric|min:0',
                'remarks' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if payroll already exists for this employee and period
            $existingPayroll = Payroll::where('employee_id', $request->employee_id)
                ->where('month', $request->month)
                ->where('year', $request->year)
                ->first();

            if ($existingPayroll) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payroll already exists for this employee and period'
                ], 422);
            }

            DB::beginTransaction();

            // Calculate net salary
            $totalDeductions = ($request->tds_amount ?? 0) + 
                              ($request->pf_amount ?? 0) + 
                              ($request->esi_amount ?? 0) + 
                              ($request->professional_tax ?? 0);
            
            $netSalary = $request->gross_salary - $totalDeductions;

            // Create payroll
            $payroll = Payroll::create([
                'employee_id' => $request->employee_id,
                'month' => $request->month,
                'year' => $request->year,
                'basic_salary' => $request->basic_salary,
                'gross_salary' => $request->gross_salary,
                'net_salary' => $netSalary,
                'tds_amount' => $request->tds_amount ?? 0,
                'pf_amount' => $request->pf_amount ?? 0,
                'esi_amount' => $request->esi_amount ?? 0,
                'professional_tax' => $request->professional_tax ?? 0,
                'total_earnings' => $request->gross_salary,
                'total_deductions' => $totalDeductions,
                'status' => 'draft',
                'remarks' => $request->remarks,
            ]);

            // Add salary components if provided
            if ($request->has('salary_components') && is_array($request->salary_components)) {
                foreach ($request->salary_components as $component) {
                    $componentValidator = Validator::make($component, [
                        'component_type' => 'required|string|max:255',
                        'amount' => 'required|numeric|min:0',
                        'is_taxable' => 'boolean',
                        'description' => 'nullable|string',
                        'calculation_basis' => 'nullable|string|max:255',
                        'calculation_value' => 'nullable|numeric',
                    ]);

                    if ($componentValidator->fails()) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Salary component validation failed',
                            'errors' => $componentValidator->errors()
                        ], 422);
                    }

                    $payroll->salaryComponents()->create($component);
                }
            }

            // Add salary deductions if provided
            if ($request->has('salary_deductions') && is_array($request->salary_deductions)) {
                foreach ($request->salary_deductions as $deduction) {
                    $deductionValidator = Validator::make($deduction, [
                        'deduction_type' => 'required|string|max:255',
                        'amount' => 'required|numeric|min:0',
                        'description' => 'nullable|string',
                        'calculation_basis' => 'nullable|string|max:255',
                        'calculation_value' => 'nullable|numeric',
                        'is_statutory' => 'boolean',
                        'is_voluntary' => 'boolean',
                    ]);

                    if ($deductionValidator->fails()) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Salary deduction validation failed',
                            'errors' => $deductionValidator->errors()
                        ], 422);
                    }

                    $payroll->deductions()->create($deduction);
                }
            }

            DB::commit();

            $payroll->load(['employee.user', 'salaryComponents', 'deductions']);

            return response()->json([
                'success' => true,
                'data' => $payroll,
                'message' => 'Payroll created successfully'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating payroll: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified payroll
     */
    public function show(Payroll $payroll): JsonResponse
    {
        try {
            $payroll->load([
                'employee.user', 
                'approvedBy', 
                'salaryComponents', 
                'deductions'
            ]);

            return response()->json([
                'success' => true,
                'data' => $payroll,
                'message' => 'Payroll retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving payroll: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified payroll
     */
    public function update(Request $request, Payroll $payroll): JsonResponse
    {
        try {
            if (!$payroll->isDraft()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft payrolls can be updated'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'basic_salary' => 'sometimes|numeric|min:0',
                'gross_salary' => 'sometimes|numeric|min:0',
                'tds_amount' => 'nullable|numeric|min:0',
                'pf_amount' => 'nullable|numeric|min:0',
                'esi_amount' => 'nullable|numeric|min:0',
                'professional_tax' => 'nullable|numeric|min:0',
                'remarks' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Update basic fields
            $payroll->update($request->only([
                'basic_salary', 'gross_salary', 'tds_amount', 'pf_amount', 
                'esi_amount', 'professional_tax', 'remarks'
            ]));

            // Recalculate net salary
            $totalDeductions = ($payroll->tds_amount ?? 0) + 
                              ($payroll->pf_amount ?? 0) + 
                              ($payroll->esi_amount ?? 0) + 
                              ($payroll->professional_tax ?? 0);
            
            $payroll->update([
                'net_salary' => $payroll->gross_salary - $totalDeductions,
                'total_deductions' => $totalDeductions,
            ]);

            DB::commit();

            $payroll->load(['employee.user', 'salaryComponents', 'deductions']);

            return response()->json([
                'success' => true,
                'data' => $payroll,
                'message' => 'Payroll updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating payroll: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified payroll
     */
    public function destroy(Payroll $payroll): JsonResponse
    {
        try {
            if (!$payroll->isDraft()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft payrolls can be deleted'
                ], 422);
            }

            $payroll->delete();

            return response()->json([
                'success' => true,
                'message' => 'Payroll deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting payroll: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve payroll
     */
    public function approve(Request $request, Payroll $payroll): JsonResponse
    {
        try {
            if (!$payroll->canBeApproved()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payroll cannot be approved in its current status'
                ], 422);
            }

            $user = auth()->user();
            $payroll->approve($user);

            $payroll->load(['employee.user', 'approvedBy']);

            return response()->json([
                'success' => true,
                'data' => $payroll,
                'message' => 'Payroll approved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error approving payroll: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process payroll
     */
    public function process(Payroll $payroll): JsonResponse
    {
        try {
            if (!$payroll->canBeProcessed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payroll cannot be processed in its current status'
                ], 422);
            }

            $payroll->process();

            $payroll->load(['employee.user']);

            return response()->json([
                'success' => true,
                'data' => $payroll,
                'message' => 'Payroll processed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing payroll: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark payroll as paid
     */
    public function markAsPaid(Request $request, Payroll $payroll): JsonResponse
    {
        try {
            if (!$payroll->canBePaid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payroll cannot be marked as paid in its current status'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'payment_reference' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $payroll->markAsPaid($request->payment_reference);

            $payroll->load(['employee.user']);

            return response()->json([
                'success' => true,
                'data' => $payroll,
                'message' => 'Payroll marked as paid successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error marking payroll as paid: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payroll statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $month = $request->get('month', now()->month);
            $year = $request->get('year', now()->year);

            $stats = [
                'total_payroll' => Payroll::getTotalPayrollByPeriod($month, $year),
                'total_payroll_year' => Payroll::getTotalPayrollByYear($year),
                'status_distribution' => Payroll::getPayrollStatusDistribution(),
                'monthly_trend' => Payroll::getMonthlyPayrollTrend($year),
                'pending_approvals' => Payroll::getPendingApprovalsCount(),
                'pending_processing' => Payroll::getPendingProcessingCount(),
                'pending_payments' => Payroll::getPendingPaymentsCount(),
                'average_salary' => Payroll::byPeriod($month, $year)->avg('net_salary') ?? 0,
                'total_employees' => Payroll::byPeriod($month, $year)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Payroll statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving payroll statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate payslip
     */
    public function generatePayslip(Payroll $payroll): JsonResponse
    {
        try {
            $payroll->load([
                'employee.user', 
                'salaryComponents', 
                'deductions'
            ]);

            $payslip = [
                'employee' => [
                    'name' => $payroll->employee->user->name,
                    'employee_id' => $payroll->employee->employee_id,
                    'designation' => $payroll->employee->designation,
                    'department' => $payroll->employee->department,
                ],
                'payroll_period' => $payroll->period,
                'basic_salary' => $payroll->basic_salary,
                'gross_salary' => $payroll->gross_salary,
                'net_salary' => $payroll->net_salary,
                'salary_breakdown' => $payroll->getSalaryBreakdown(),
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ];

            return response()->json([
                'success' => true,
                'data' => $payslip,
                'message' => 'Payslip generated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating payslip: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk payroll operations
     */
    public function bulkOperations(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'operation' => ['required', Rule::in(['approve', 'process', 'mark_paid'])],
                'payroll_ids' => 'required|array',
                'payroll_ids.*' => 'exists:payrolls,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $payrolls = Payroll::whereIn('id', $request->payroll_ids)->get();
            $successCount = 0;
            $failedCount = 0;
            $errors = [];

            foreach ($payrolls as $payroll) {
                try {
                    switch ($request->operation) {
                        case 'approve':
                            if ($payroll->canBeApproved()) {
                                $payroll->approve(auth()->user());
                                $successCount++;
                            } else {
                                $failedCount++;
                                $errors[] = "Payroll ID {$payroll->id}: Cannot be approved";
                            }
                            break;
                        case 'process':
                            if ($payroll->canBeProcessed()) {
                                $payroll->process();
                                $successCount++;
                            } else {
                                $failedCount++;
                                $errors[] = "Payroll ID {$payroll->id}: Cannot be processed";
                            }
                            break;
                        case 'mark_paid':
                            if ($payroll->canBePaid()) {
                                $payroll->markAsPaid();
                                $successCount++;
                            } else {
                                $failedCount++;
                                $errors[] = "Payroll ID {$payroll->id}: Cannot be marked as paid";
                            }
                            break;
                    }
                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = "Payroll ID {$payroll->id}: " . $e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'operation' => $request->operation,
                    'total_processed' => count($request->payroll_ids),
                    'success_count' => $successCount,
                    'failed_count' => $failedCount,
                    'errors' => $errors,
                ],
                'message' => "Bulk operation completed. {$successCount} successful, {$failedCount} failed."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error performing bulk operations: ' . $e->getMessage()
            ], 500);
        }
    }
} 