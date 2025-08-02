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