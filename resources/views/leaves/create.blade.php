@extends('layouts.app')

@section('title', 'Apply for Leave')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-plus me-2"></i>Apply for Leave
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Employee Selection (for HR/Manager/Super Admin) -->
                    @if($availableUsers->count() > 1)
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-user-plus me-1"></i>Select Employee
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <label for="user_id" class="form-label">Employee <span class="text-danger">*</span></label>
                                            <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                                @foreach($availableUsers as $availableUser)
                                                <option value="{{ $availableUser->id }}" {{ $targetUser->id == $availableUser->id ? 'selected' : '' }}>
                                                    {{ $availableUser->name }} 
                                                    @if($availableUser->organization)
                                                        ({{ $availableUser->organization->name }})
                                                    @endif
                                                </option>
                                                @endforeach
                                            </select>
                                            @error('user_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4 d-flex align-items-end">
                                            <button type="button" class="btn btn-outline-primary" onclick="loadLeaveBalances()">
                                                <i class="fas fa-sync-alt me-1"></i>Load Leave Balance
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <input type="hidden" name="user_id" value="{{ $targetUser->id }}">
                    @endif

                    <!-- Leave Balance Summary -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted mb-3">
                                <i class="fas fa-balance-scale me-1"></i>
                                @if($targetUser->id == auth()->id())
                                    Your Leave Balance
                                @else
                                    {{ $targetUser->name }}'s Leave Balance
                                @endif
                            </h6>
                            <div class="row" id="leave-balances-container">
                                @forelse($leaveBalances as $balance)
                                <div class="col-md-4 mb-3">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body text-center">
                                            <div class="d-flex align-items-center justify-content-center mb-2">
                                                <div class="rounded-circle p-2 me-2" style="background-color: {{ $balance->leaveType->color }}; width: 40px; height: 40px;">
                                                    <i class="fas fa-calendar text-white"></i>
                                                </div>
                                                <h6 class="mb-0">{{ $balance->leaveType->name }}</h6>
                                            </div>
                                            <div class="row text-center">
                                                <div class="col-4">
                                                    <div class="text-primary fw-bold">{{ $balance->total_days }}</div>
                                                    <small class="text-muted">Total</small>
                                                </div>
                                                <div class="col-4">
                                                    <div class="text-warning fw-bold">{{ $balance->used_days }}</div>
                                                    <small class="text-muted">Used</small>
                                                </div>
                                                <div class="col-4">
                                                    <div class="text-success fw-bold">{{ $balance->remaining_days }}</div>
                                                    <small class="text-muted">Remaining</small>
                                                </div>
                                            </div>
                                            <div class="progress mt-2" style="height: 6px;">
                                                <div class="progress-bar" role="progressbar" 
                                                     style="width: {{ $balance->usage_percentage }}%; background-color: {{ $balance->leaveType->color }};"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-1"></i>
                                        No leave balances found for the selected employee. Please contact HR to set up leave entitlements.
                                    </div>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Leave Application Form -->
                    <form action="{{ route('leaves.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="leave_type_id" class="form-label">Leave Type <span class="text-danger">*</span></label>
                                    <select name="leave_type_id" id="leave_type_id" class="form-select @error('leave_type_id') is-invalid @enderror" required>
                                        <option value="">Select Leave Type</option>
                                        @foreach($leaveTypes as $leaveType)
                                        <option value="{{ $leaveType->id }}" {{ old('leave_type_id') == $leaveType->id ? 'selected' : '' }}>
                                            {{ $leaveType->name }}
                                            @if($leaveType->requires_approval)
                                                (Requires Approval)
                                            @else
                                                (Auto-Approved)
                                            @endif
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('leave_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="total_days" class="form-label">Total Days</label>
                                    <input type="text" id="total_days" class="form-control" readonly>
                                    <small class="text-muted">Calculated automatically based on date range</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" 
                                           value="{{ old('start_date') }}" min="{{ date('Y-m-d') }}" required>
                                    @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror" 
                                           value="{{ old('end_date') }}" min="{{ date('Y-m-d') }}" required>
                                    @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason for Leave <span class="text-danger">*</span></label>
                            <textarea name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror" 
                                      rows="4" placeholder="Please provide a detailed reason for your leave request..." required>{{ old('reason') }}</textarea>
                            @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Validation Messages -->
                        @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('leaves.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back to Leave List
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i>Submit Leave Request
                            </button>
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
document.addEventListener('DOMContentLoaded', function() {
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    const totalDays = document.getElementById('total_days');

    function calculateDays() {
        if (startDate.value && endDate.value) {
            const start = new Date(startDate.value);
            const end = new Date(endDate.value);
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            totalDays.value = diffDays + ' day(s)';
        } else {
            totalDays.value = '';
        }
    }

    startDate.addEventListener('change', function() {
        endDate.min = this.value;
        calculateDays();
    });

    endDate.addEventListener('change', function() {
        calculateDays();
    });
});

// Function to load leave balances for selected user
function loadLeaveBalances() {
    const userId = document.getElementById('user_id').value;
    const container = document.getElementById('leave-balances-container');
    
    // Show loading state
    container.innerHTML = '<div class="col-12 text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    
    // Fetch leave balances via AJAX
    fetch(`/api/leave-balances/${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                container.innerHTML = data.html;
            } else {
                container.innerHTML = '<div class="col-12"><div class="alert alert-danger">Failed to load leave balances.</div></div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<div class="col-12"><div class="alert alert-danger">Failed to load leave balances.</div></div>';
        });
}

// Auto-load leave balances when user selection changes
document.addEventListener('DOMContentLoaded', function() {
    const userSelect = document.getElementById('user_id');
    if (userSelect) {
        userSelect.addEventListener('change', loadLeaveBalances);
    }
});
</script>
@endpush 