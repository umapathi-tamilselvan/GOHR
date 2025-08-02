<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Leave Management') }}
        </h2>
    </x-slot>

    <div>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>Leave Management
                    </h5>
                    <div class="btn-group">
                        @can('create', App\Models\Leave::class)
                        <a href="{{ route('leaves.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Apply Leave
                        </a>
                        @endcan
                        @if(auth()->user()->hasAnyRole(['super-admin', 'hr', 'manager']))
                        <a href="{{ route('leaves.create') }}?user_id=" class="btn btn-outline-primary" id="apply-for-others-btn">
                            <i class="fas fa-user-plus me-1"></i>Apply for Others
                        </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form method="GET" action="{{ route('leaves.index') }}" class="row g-3">
                                <div class="col-md-2">
                                    <label for="status" class="form-label">Status</label>
                                    <select name="status" id="status" class="form-select">
                                        <option value="">All Status</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="leave_type_id" class="form-label">Leave Type</label>
                                    <select name="leave_type_id" id="leave_type_id" class="form-select">
                                        <option value="">All Types</option>
                                        @foreach($leaveTypes as $leaveType)
                                        <option value="{{ $leaveType->id }}" {{ request('leave_type_id') == $leaveType->id ? 'selected' : '' }}>
                                            {{ $leaveType->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="date_from" class="form-label">From Date</label>
                                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                                </div>
                                <div class="col-md-2">
                                    <label for="date_to" class="form-label">To Date</label>
                                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                                </div>
                                <div class="col-md-2">
                                    <label for="search" class="form-label">Search</label>
                                    <input type="text" name="search" id="search" class="form-control" placeholder="Employee name or email" value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-search me-1"></i>Filter
                                    </button>
                                    <a href="{{ route('leaves.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>Clear
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="btn-group" role="group">
                                <a href="{{ route('leaves.calendar') }}" class="btn btn-outline-info">
                                    <i class="fas fa-calendar me-1"></i>Calendar View
                                </a>
                                <a href="{{ route('leaves.report') }}" class="btn btn-outline-success">
                                    <i class="fas fa-chart-bar me-1"></i>Reports
                                </a>
                                <a href="{{ route('leave-balances.index') }}" class="btn btn-outline-warning">
                                    <i class="fas fa-balance-scale me-1"></i>Leave Balances
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Leave List -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>Date Range</th>
                                    <th>Days</th>
                                    <th>Status</th>
                                    <th>Applied On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leaves as $leave)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <span class="text-white fw-bold">{{ substr($leave->user->name, 0, 1) }}</span>
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $leave->user->name }}</div>
                                                <small class="text-muted">{{ $leave->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $leave->leaveType->color }}; color: white;">
                                            {{ $leave->leaveType->name }}
                                        </span>
                                    </td>
                                    <td>
                                        <div>{{ $leave->start_date->format('M d, Y') }}</div>
                                        <div class="text-muted">to {{ $leave->end_date->format('M d, Y') }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $leave->total_days }} days</span>
                                    </td>
                                    <td>
                                        {!! $leave->status_badge !!}
                                    </td>
                                    <td>
                                        <div>{{ $leave->created_at->format('M d, Y') }}</div>
                                        <small class="text-muted">{{ $leave->created_at->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('leaves.show', $leave) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @can('update', $leave)
                                            <a href="{{ route('leaves.edit', $leave) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcan
                                            @can('approve', $leave)
                                            @if($leave->isPending())
                                            <form action="{{ route('leaves.approve', $leave) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Approve" onclick="return confirm('Are you sure you want to approve this leave?')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            @endif
                                            @endcan
                                            @can('delete', $leave)
                                            @if($leave->isPending())
                                            <form action="{{ route('leaves.destroy', $leave) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Cancel" onclick="return confirm('Are you sure you want to cancel this leave?')">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                            @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-calendar-times fa-3x mb-3"></i>
                                            <h5>No leave requests found</h5>
                                            <p>No leave requests match your current filters.</p>
                                            @can('create', App\Models\Leave::class)
                                            <a href="{{ route('leaves.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus me-1"></i>Apply for Leave
                                            </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $leaves->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Leave Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="manager_comment" class="form-label">Rejection Reason</label>
                        <textarea name="manager_comment" id="manager_comment" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Leave</button>
                </div>
            </form>
        </div>
    </div>
</div>

    </div>
</x-app-layout>

@push('scripts')
<script>
function rejectLeave(leaveId) {
    document.getElementById('rejectForm').action = `/leaves/${leaveId}/reject`;
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

// Handle "Apply for Others" button
document.addEventListener('DOMContentLoaded', function() {
    const applyForOthersBtn = document.getElementById('apply-for-others-btn');
    if (applyForOthersBtn) {
        applyForOthersBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Create a modal to select user
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.id = 'selectUserModal';
            modal.innerHTML = `
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Select Employee</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="select-user" class="form-label">Choose Employee</label>
                                <select id="select-user" class="form-select">
                                    <option value="">Select an employee...</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" onclick="proceedToApplyLeave()">Continue</button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Load available users
            loadAvailableUsers();
            
            // Show modal
            new bootstrap.Modal(modal).show();
        });
    }
});

function loadAvailableUsers() {
    const userSelect = document.getElementById('select-user');
    const currentUser = {{ auth()->id() }};
    
    // This would typically be an AJAX call to get available users
    // For now, we'll redirect to the create page with a parameter
    // that will trigger the user selection
    window.location.href = '{{ route("leaves.create") }}';
}

function proceedToApplyLeave() {
    const selectedUserId = document.getElementById('select-user').value;
    if (selectedUserId) {
        window.location.href = `{{ route('leaves.create') }}?user_id=${selectedUserId}`;
    }
}
</script>
@endpush 