@extends('layouts.app')

@section('title', 'Leave Details')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-check me-2"></i>Leave Details
                    </h5>
                    <div class="btn-group">
                        @can('update', $leave)
                        <a href="{{ route('leaves.edit', $leave) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
                        @endcan
                        <a href="{{ route('leaves.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Status Banner -->
                    <div class="alert alert-{{ $leave->status == 'pending' ? 'warning' : ($leave->status == 'approved' ? 'success' : ($leave->status == 'rejected' ? 'danger' : 'secondary')) }} mb-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-{{ $leave->status == 'pending' ? 'clock' : ($leave->status == 'approved' ? 'check-circle' : ($leave->status == 'rejected' ? 'times-circle' : 'ban')) }} me-2"></i>
                            <div>
                                <strong>Status: {{ ucfirst($leave->status) }}</strong>
                                @if($leave->approved_at)
                                <br><small>Processed on {{ $leave->approved_at->format('M d, Y h:i A') }}</small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Employee Information -->
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-muted mb-3">
                                        <i class="fas fa-user me-1"></i>Employee Information
                                    </h6>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar-lg bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                            <span class="text-white fw-bold fs-4">{{ substr($leave->user->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <h5 class="mb-1">{{ $leave->user->name }}</h5>
                                            <p class="text-muted mb-0">{{ $leave->user->email }}</p>
                                            <small class="text-muted">{{ $leave->user->organization->name }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Leave Information -->
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-muted mb-3">
                                        <i class="fas fa-calendar me-1"></i>Leave Information
                                    </h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="mb-2">
                                                <small class="text-muted">Leave Type</small>
                                                <div>
                                                    <span class="badge" style="background-color: {{ $leave->leaveType->color }}; color: white;">
                                                        {{ $leave->leaveType->name }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="mb-2">
                                                <small class="text-muted">Total Days</small>
                                                <div class="fw-bold">{{ $leave->total_days }} days</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="mb-2">
                                                <small class="text-muted">Start Date</small>
                                                <div class="fw-bold">{{ $leave->start_date->format('M d, Y') }}</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="mb-2">
                                                <small class="text-muted">End Date</small>
                                                <div class="fw-bold">{{ $leave->end_date->format('M d, Y') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Leave Details -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-file-alt me-1"></i>Leave Details
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label text-muted">Reason for Leave</label>
                                                <div class="border rounded p-3 bg-light">
                                                    {{ $leave->reason }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label text-muted">Applied On</label>
                                                <div class="border rounded p-3 bg-light">
                                                    {{ $leave->created_at->format('M d, Y h:i A') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @if($leave->manager_comment)
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Manager's Comment</label>
                                        <div class="border rounded p-3 bg-light">
                                            {{ $leave->manager_comment }}
                                        </div>
                                    </div>
                                    @endif

                                    @if($leave->approvedBy)
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Processed By</label>
                                        <div class="border rounded p-3 bg-light">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-success rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    <span class="text-white fw-bold">{{ substr($leave->approvedBy->name, 0, 1) }}</span>
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ $leave->approvedBy->name }}</div>
                                                    <small class="text-muted">{{ $leave->approvedBy->email }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Approval Actions -->
                    @can('approve', $leave)
                    @if($leave->isPending())
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0">
                                        <i class="fas fa-tasks me-1"></i>Approval Actions
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <form action="{{ route('leaves.approve', $leave) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success w-100" onclick="return confirm('Are you sure you want to approve this leave request?')">
                                                    <i class="fas fa-check me-1"></i>Approve Leave
                                                </button>
                                            </form>
                                        </div>
                                        <div class="col-md-6">
                                            <button type="button" class="btn btn-danger w-100" onclick="rejectLeave({{ $leave->id }})">
                                                <i class="fas fa-times me-1"></i>Reject Leave
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endcan

                    <!-- Leave Balance Impact -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-balance-scale me-1"></i>Leave Balance Impact
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-1"></i>
                                        This leave request will consume <strong>{{ $leave->total_days }} days</strong> from the employee's {{ $leave->leaveType->name }} balance.
                                        @if($leave->isPending())
                                        The balance will be updated once the leave is approved.
                                        @elseif($leave->isApproved())
                                        The balance has been updated accordingly.
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
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
                        <label for="manager_comment" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="manager_comment" id="manager_comment" class="form-control" rows="3" required 
                                  placeholder="Please provide a reason for rejecting this leave request..."></textarea>
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
@endsection

@push('scripts')
<script>
function rejectLeave(leaveId) {
    document.getElementById('rejectForm').action = `/leaves/${leaveId}/reject`;
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}
</script>
@endpush 