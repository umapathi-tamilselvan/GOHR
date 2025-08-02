<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Attendance Details') }}
            </h2>
            <div>
                @can('update', $attendance)
                    <a href="{{ route('attendances.edit', $attendance) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i> {{ __('Edit Attendance') }}
                    </a>
                @endcan
                @can('delete', $attendance)
                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete()">
                        <i class="fas fa-trash"></i> {{ __('Delete Attendance') }}
                    </button>
                    <form id="delete-form" method="POST" action="{{ route('attendances.destroy', $attendance) }}" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                @endcan
                <a href="{{ route('attendances.list') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> {{ __('Back to Attendance List') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="row">
                        <div class="col-12 col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-clock"></i> {{ __('Attendance Information') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">{{ __('Employee') }}</label>
                                                <p class="form-control-plaintext">{{ $attendance->user->name }}</p>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">{{ __('Organization') }}</label>
                                                <p class="form-control-plaintext">{{ $attendance->user->organization->name ?? __('Not Assigned') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">{{ __('Date') }}</label>
                                                <p class="form-control-plaintext">{{ $attendance->date->format('F j, Y') }}</p>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">{{ __('Status') }}</label>
                                                <p class="form-control-plaintext">
                                                    @if($attendance->status === 'Full Day')
                                                        <span class="badge bg-success">{{ $attendance->status }}</span>
                                                    @elseif($attendance->status === 'Half Day')
                                                        <span class="badge bg-warning">{{ $attendance->status }}</span>
                                                    @else
                                                        <span class="badge bg-danger">{{ $attendance->status }}</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">{{ __('Check In Time') }}</label>
                                                <p class="form-control-plaintext">
                                                    @if($attendance->check_in)
                                                        {{ $attendance->check_in->format('H:i') }}
                                                        <small class="text-muted">({{ $attendance->check_in->format('M j, Y') }})</small>
                                                    @else
                                                        <span class="text-muted">{{ __('Not checked in') }}</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">{{ __('Check Out Time') }}</label>
                                                <p class="form-control-plaintext">
                                                    @if($attendance->check_out)
                                                        {{ $attendance->check_out->format('H:i') }}
                                                        <small class="text-muted">({{ $attendance->check_out->format('M j, Y') }})</small>
                                                    @else
                                                        <span class="text-muted">{{ __('Not checked out') }}</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">{{ __('Worked Hours') }}</label>
                                                <p class="form-control-plaintext">
                                                    @if($attendance->worked_minutes)
                                                        {{ number_format($attendance->worked_minutes / 60, 1) }} {{ __('hours') }}
                                                        <small class="text-muted">({{ $attendance->worked_minutes }} {{ __('minutes') }})</small>
                                                    @else
                                                        <span class="text-muted">{{ __('No hours recorded') }}</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">{{ __('Record Created') }}</label>
                                                <p class="form-control-plaintext">{{ $attendance->created_at->format('F j, Y \a\t H:i') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12 col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-chart-bar"></i> {{ __('Work Summary') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($attendance->worked_minutes)
                                        <div class="text-center mb-3">
                                            <div class="h4 text-primary">{{ number_format($attendance->worked_minutes / 60, 1) }}</div>
                                            <small class="text-muted">{{ __('Hours Worked') }}</small>
                                        </div>
                                        
                                        @if($attendance->check_in && $attendance->check_in->format('H:i') > '09:00')
                                            <div class="alert alert-warning alert-sm">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                {{ __('Late arrival') }}
                                            </div>
                                        @endif
                                        
                                        @if($attendance->worked_minutes < 240)
                                            <div class="alert alert-danger alert-sm">
                                                <i class="fas fa-times-circle"></i>
                                                {{ __('Incomplete day') }}
                                            </div>
                                        @elseif($attendance->worked_minutes < 480)
                                            <div class="alert alert-warning alert-sm">
                                                <i class="fas fa-clock"></i>
                                                {{ __('Half day') }}
                                            </div>
                                        @else
                                            <div class="alert alert-success alert-sm">
                                                <i class="fas fa-check-circle"></i>
                                                {{ __('Full day completed') }}
                                            </div>
                                        @endif
                                    @else
                                        <div class="text-center">
                                            <div class="h4 text-muted">-</div>
                                            <small class="text-muted">{{ __('No work hours') }}</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete() {
            if (confirm('{{ __("Are you sure you want to delete this attendance record? This action cannot be undone.") }}')) {
                document.getElementById('delete-form').submit();
            }
        }
    </script>
</x-app-layout> 