<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\LeaveRequestStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = LeaveRequest::with(['user.organization', 'approver']);

        // Filter based on user role and workflow
        if ($user->hasRole('Super Admin')) {
            // Super Admin can see all leave requests
        } elseif ($user->hasRole('Manager')) {
            // Managers can see Employee leave requests from their organization
            $organizationUsers = User::where('organization_id', $user->organization_id)->pluck('id');
            $query->whereIn('user_id', $organizationUsers)
                  ->whereHas('user', function ($q) {
                      $q->role('Employee');
                  });
        } elseif ($user->hasRole('HR')) {
            // HR can see Manager leave requests from their organization
            $organizationUsers = User::where('organization_id', $user->organization_id)->pluck('id');
            $query->whereIn('user_id', $organizationUsers)
                  ->whereHas('user', function ($q) {
                      $q->role('Manager');
                  });
        } else {
            // Regular employees can only see their own requests
            $query->where('user_id', $user->id);
        }

        // Apply status filter
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $leaveRequests = $query->latest()->paginate(10);

        // Determine role color for UI
        $roleColor = 'blue';
        if ($user->hasRole('Super Admin')) {
            $roleColor = 'red';
        } elseif ($user->hasRole('HR')) {
            $roleColor = 'purple';
        } elseif ($user->hasRole('Manager')) {
            $roleColor = 'green';
        }

        return view('leave_requests.index', compact('leaveRequests', 'roleColor'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        $roleColor = 'blue';
        if ($user->hasRole('Super Admin')) {
            $roleColor = 'red';
        } elseif ($user->hasRole('HR')) {
            $roleColor = 'purple';
        } elseif ($user->hasRole('Manager')) {
            $roleColor = 'green';
        }

        return view('leave_requests.create', compact('roleColor'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'leave_type' => 'required|string',
            'start_date' => 'required|date|after_or_equal:' . Carbon::today()->toDateString(),
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:500',
        ]);

        $leaveRequest = LeaveRequest::create([
            'user_id' => Auth::id(),
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->route('leave-requests.index')
            ->with('success', 'Leave request submitted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveRequest $leaveRequest)
    {
        $this->authorize('view', $leaveRequest);
        
        $leaveRequest->load('user');

        return view('leave_requests.show', compact('leaveRequest'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LeaveRequest $leaveRequest)
    {
        $this->authorize('update', $leaveRequest);

        $user = Auth::user();
        $roleColor = 'blue';
        if ($user->hasRole('Super Admin')) {
            $roleColor = 'red';
        } elseif ($user->hasRole('HR')) {
            $roleColor = 'purple';
        } elseif ($user->hasRole('Manager')) {
            $roleColor = 'green';
        }

        return view('leave_requests.edit', compact('leaveRequest', 'roleColor'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        $this->authorize('update', $leaveRequest);

        $request->validate([
            'leave_type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:500',
        ]);

        $leaveRequest->update([
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
        ]);

        return redirect()->route('leave-requests.index')
            ->with('success', 'Leave request updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveRequest $leaveRequest)
    {
        $this->authorize('delete', $leaveRequest);

        $leaveRequest->delete();

        return redirect()->route('leave-requests.index')
            ->with('success', 'Leave request deleted successfully.');
    }

    /**
     * Approve or reject a leave request.
     */
    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        $this->authorize('approve', $leaveRequest);

        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $updateData = [
            'status' => $request->status,
        ];

        // If approved, track the approver and approval time
        if ($request->status === 'approved') {
            $updateData['approver_id'] = Auth::id();
            $updateData['approved_at'] = now();
        }

        $leaveRequest->update($updateData);

        // Send notification to the leave request applicant
        $leaveRequest->user->notify(new LeaveRequestStatusChanged($leaveRequest, $request->status));

        $statusText = $request->status === 'approved' ? 'approved' : 'rejected';
        return redirect()->route('leave-requests.index')
            ->with('success', "Leave request {$statusText} successfully.");
    }
}
