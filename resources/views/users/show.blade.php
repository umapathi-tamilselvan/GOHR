<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h4 mb-1 text-dark">
                    {{ __('User Details') }}
                </h2>
                <p class="text-muted mb-0">View detailed information about {{ $user->name }}</p>
            </div>
            <div class="btn-group" role="group">
                @can('update', $user)
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit me-2"></i>{{ __('Edit User') }}
                    </a>
                @endcan
                @can('delete', $user)
                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete()">
                        <i class="fas fa-trash me-2"></i>{{ __('Delete User') }}
                    </button>
                    <form id="delete-form" method="POST" action="{{ route('users.destroy', $user) }}" class="d-none">
                        @csrf
                        @method('DELETE')
                    </form>
                @endcan
                <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left me-2"></i>{{ __('Back to Users') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">
            <div class="row">
                <!-- User Information Card -->
                <div class="col-12 col-lg-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user me-2"></i>{{ __('User Information') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-dark">{{ __('Name') }}</label>
                                        <p class="form-control-plaintext">{{ $user->name }}</p>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-dark">{{ __('Email') }}</label>
                                        <p class="form-control-plaintext">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-dark">{{ __('Organization') }}</label>
                                        <p class="form-control-plaintext">{{ $user->organization->name ?? __('Not Assigned') }}</p>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-dark">{{ __('Role') }}</label>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($user->roles as $role)
                                                <span class="badge bg-primary">{{ $role->name }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-dark">{{ __('Member Since') }}</label>
                                        <p class="form-control-plaintext">{{ $user->created_at->format('F j, Y') }}</p>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-dark">{{ __('Last Updated') }}</label>
                                        <p class="form-control-plaintext">{{ $user->updated_at->format('F j, Y') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Stats Card -->
                <div class="col-12 col-lg-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-bar me-2"></i>{{ __('Quick Stats') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <div class="h2 text-primary mb-1">{{ $user->attendances->count() }}</div>
                                <small class="text-muted">{{ __('Total Attendance Records') }}</small>
                            </div>
                            
                            <div class="text-center mb-4">
                                <div class="h2 text-success mb-1">{{ $user->attendances->where('status', 'Full Day')->count() }}</div>
                                <small class="text-muted">{{ __('Full Days') }}</small>
                            </div>
                            
                            <div class="text-center">
                                <div class="h2 text-warning mb-1">{{ $user->attendances->where('status', 'Half Day')->count() }}</div>
                                <small class="text-muted">{{ __('Half Days') }}</small>
                            </div>
                        </div>
                    </div>

                    <!-- User Avatar Card -->
                    <div class="card shadow-sm">
                        <div class="card-body text-center">
                            <div class="avatar avatar-xl bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <span class="text-white fw-bold h3">{{ substr($user->name, 0, 1) }}</span>
                            </div>
                            <h5 class="card-title">{{ $user->name }}</h5>
                            <p class="text-muted">{{ $user->email }}</p>
                            <div class="d-flex justify-content-center">
                                <span class="badge bg-success">
                                    <i class="fas fa-circle me-1"></i>Active
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Attendance Records -->
            @if($user->attendances->count() > 0)
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-clock me-2"></i>{{ __('Recent Attendance Records') }}
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="border-0">{{ __('Date') }}</th>
                                            <th class="border-0">{{ __('Check In') }}</th>
                                            <th class="border-0">{{ __('Check Out') }}</th>
                                            <th class="border-0">{{ __('Worked Hours') }}</th>
                                            <th class="border-0">{{ __('Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($user->attendances->take(10) as $attendance)
                                        <tr>
                                            <td>{{ $attendance->date->format('M j, Y') }}</td>
                                            <td>{{ $attendance->check_in ? $attendance->check_in->format('H:i') : '-' }}</td>
                                            <td>{{ $attendance->check_out ? $attendance->check_out->format('H:i') : '-' }}</td>
                                            <td>
                                                @if($attendance->worked_minutes)
                                                    {{ number_format($attendance->worked_minutes / 60, 1) }} {{ __('hours') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if($attendance->status === 'Full Day')
                                                    <span class="badge bg-success">{{ $attendance->status }}</span>
                                                @elseif($attendance->status === 'Half Day')
                                                    <span class="badge bg-warning">{{ $attendance->status }}</span>
                                                @else
                                                    <span class="badge bg-danger">{{ $attendance->status }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @if($user->attendances->count() > 10)
                        <div class="card-footer bg-white border-top">
                            <div class="text-center">
                                <a href="#" class="btn btn-outline-primary btn-sm">
                                    {{ __('View All Attendance Records') }}
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Additional Information Cards -->
            <div class="row mt-4">
                <div class="col-12 col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-calendar-alt me-2"></i>{{ __('Leave Information') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center">
                                <div class="h3 text-info mb-2">{{ $user->leaves->count() }}</div>
                                <p class="text-muted mb-0">{{ __('Total Leave Requests') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-tasks me-2"></i>{{ __('Project Information') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center">
                                <div class="h3 text-purple mb-2">{{ $user->projectMembers->count() }}</div>
                                <p class="text-muted mb-0">{{ __('Projects Assigned') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete() {
            if (confirm('{{ __("Are you sure you want to delete this user? This action cannot be undone.") }}')) {
                document.getElementById('delete-form').submit();
            }
        }
    </script>

    <style>
        .avatar {
            width: 40px;
            height: 40px;
        }
        .avatar-xl {
            width: 80px;
            height: 80px;
        }
        .table > :not(caption) > * > * {
            padding: 1rem 0.75rem;
        }
        .form-control-plaintext {
            color: #6c757d;
            font-weight: 500;
        }
        .card {
            border: 1px solid rgba(0,0,0,.125);
        }
        .card-header {
            background-color: #f8f9fa;
        }
    </style>
</x-app-layout> 