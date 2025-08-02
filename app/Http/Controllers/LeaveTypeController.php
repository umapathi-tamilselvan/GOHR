<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LeaveTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $user = Auth::user();
        $query = LeaveType::with('organization');

        if (!$user->hasRole('super-admin')) {
            $query->where('organization_id', $user->organization_id);
        }

        $leaveTypes = $query->orderBy('name')->paginate(15);

        return view('leave-types.index', compact('leaveTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('leave-types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'default_days' => 'required|integer|min:0|max:365',
            'requires_approval' => 'boolean',
            'is_active' => 'boolean',
            'color' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
        ]);

        $user = Auth::user();
        
        LeaveType::create([
            'name' => $request->name,
            'description' => $request->description,
            'default_days' => $request->default_days,
            'requires_approval' => $request->boolean('requires_approval'),
            'is_active' => $request->boolean('is_active', true),
            'color' => $request->color,
            'organization_id' => $user->organization_id,
        ]);

        return redirect()->route('leave-types.index')
            ->with('success', 'Leave type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveType $leaveType): View
    {
        $this->authorize('view', $leaveType);
        
        $leaveType->load(['organization', 'leaves.user', 'leaveBalances.user']);
        
        return view('leave-types.show', compact('leaveType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LeaveType $leaveType): View
    {
        $this->authorize('update', $leaveType);
        
        return view('leave-types.edit', compact('leaveType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveType $leaveType): RedirectResponse
    {
        $this->authorize('update', $leaveType);

        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'default_days' => 'required|integer|min:0|max:365',
            'requires_approval' => 'boolean',
            'is_active' => 'boolean',
            'color' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
        ]);

        $leaveType->update([
            'name' => $request->name,
            'description' => $request->description,
            'default_days' => $request->default_days,
            'requires_approval' => $request->boolean('requires_approval'),
            'is_active' => $request->boolean('is_active'),
            'color' => $request->color,
        ]);

        return redirect()->route('leave-types.show', $leaveType)
            ->with('success', 'Leave type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveType $leaveType): RedirectResponse
    {
        $this->authorize('delete', $leaveType);

        // Check if leave type has associated leaves
        if ($leaveType->leaves()->exists()) {
            return back()->withErrors(['leave_type' => 'Cannot delete leave type with associated leaves.']);
        }

        $leaveType->delete();

        return redirect()->route('leave-types.index')
            ->with('success', 'Leave type deleted successfully.');
    }
}
