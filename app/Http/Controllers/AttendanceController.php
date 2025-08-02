<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();

        return view('attendance.index', compact('attendance'));
    }

    public function list(Request $request)
    {
        $this->authorize('viewAny', Attendance::class);

        $attendances = Attendance::with('user.organization')
            ->when($request->filled('start_date'), function ($query) use ($request) {
                $query->where('date', '>=', $request->start_date);
            })
            ->when($request->filled('end_date'), function ($query) use ($request) {
                $query->where('date', '<=', $request->end_date);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(10);

        return view('attendances.list', compact('attendances'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Attendance $attendance)
    {
        $this->authorize('view', $attendance);

        $attendance->load('user.organization');

        return view('attendance.show', compact('attendance'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attendance $attendance)
    {
        $this->authorize('update', $attendance);

        $organizationUsers = User::where('organization_id', Auth::user()->organization_id)->get();

        return view('attendance.edit', compact('attendance', 'organizationUsers'));
    }

    public function manage()
    {
        $this->authorize('create', Attendance::class);

        $organizationUsers = User::where('organization_id', Auth::user()->organization_id)->get();

        return view('attendance.manage', compact('organizationUsers'));
    }

    public function storeManual(Request $request)
    {
        $this->authorize('create', Attendance::class);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'check_in' => 'required|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i|after:check_in',
        ]);

        $checkIn = Carbon::parse($request->date . ' ' . $request->check_in);
        $checkOut = $request->check_out ? Carbon::parse($request->date . ' ' . $request->check_out) : null;
        $workedMinutes = $checkOut ? (int) $checkIn->diffInMinutes($checkOut) : 0;

        $status = 'Incomplete';
        if ($workedMinutes >= 480) {
            $status = 'Full Day';
        } elseif ($workedMinutes >= 240) {
            $status = 'Half Day';
        }

        Attendance::updateOrCreate(
            [
                'user_id' => $request->user_id,
                'date' => $request->date,
            ],
            [
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'worked_minutes' => $workedMinutes,
                'status' => $status,
            ]
        );

        return redirect()->route('attendances.list')->with('success', 'Attendance record saved successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Attendance $attendance)
    {
        $this->authorize('update', $attendance);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'check_in' => 'required|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i|after:check_in',
        ]);

        $checkIn = Carbon::parse($request->date . ' ' . $request->check_in);
        $checkOut = $request->check_out ? Carbon::parse($request->date . ' ' . $request->check_out) : null;
        $workedMinutes = $checkOut ? (int) $checkIn->diffInMinutes($checkOut) : 0;

        $status = 'Incomplete';
        if ($workedMinutes >= 480) {
            $status = 'Full Day';
        } elseif ($workedMinutes >= 240) {
            $status = 'Half Day';
        }

        $attendance->update([
            'user_id' => $request->user_id,
            'date' => $request->date,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'worked_minutes' => $workedMinutes,
            'status' => $status,
        ]);

        return redirect()->route('attendances.list')->with('success', 'Attendance record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attendance $attendance)
    {
        $this->authorize('delete', $attendance);

        $attendance->delete();

        return redirect()->route('attendances.list')->with('success', 'Attendance record deleted successfully.');
    }

    public function report(Request $request)
    {
        $this->authorize('viewAny', Attendance::class);

        $user = Auth::user();
        $query = User::query();

        if ($user->hasRole('HR') || $user->hasRole('Manager')) {
            $query->where('organization_id', $user->organization_id);
        }

        $employees = $query->with(['attendances' => function ($q) {
            $q->whereMonth('date', Carbon::now()->month);
        }])->get();

        $reportData = $employees->map(function ($employee) {
            $lateEntries = $employee->attendances->filter(function ($attendance) {
                return $attendance->check_in?->format('H:i') > '09:00';
            })->count();

            return [
                'name' => $employee->name,
                'monthly_total' => $employee->attendances->count(),
                'late_entries' => $lateEntries,
            ];
        });

        return view('attendance.report', compact('reportData'));
    }
}
