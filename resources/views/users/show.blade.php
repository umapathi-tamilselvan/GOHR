<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('User Details') }}
            </h2>
            <div>
                @can('update', $user)
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i> {{ __('Edit User') }}
                    </a>
                @endcan
                @can('delete', $user)
                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete()">
                        <i class="fas fa-trash"></i> {{ __('Delete User') }}
                    </button>
                    <form id="delete-form" method="POST" action="{{ route('users.destroy', $user) }}" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                @endcan
                <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> {{ __('Back to Users') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <!-- User Information Card -->
                    <div class="row">
                        <div class="col-12 col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-user"></i> {{ __('User Information') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">{{ __('Name') }}</label>
                                                <p class="form-control-plaintext">{{ $user->name }}</p>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">{{ __('Email') }}</label>
                                                <p class="form-control-plaintext">{{ $user->email }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">{{ __('Organization') }}</label>
                                                <p class="form-control-plaintext">{{ $user->organization->name ?? __('Not Assigned') }}</p>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">{{ __('Role') }}</label>
                                                <p class="form-control-plaintext">
                                                    @foreach($user->roles as $role)
                                                        <span class="badge bg-primary">{{ $role->name }}</span>
                                                    @endforeach
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">{{ __('Member Since') }}</label>
                                                <p class="form-control-plaintext">{{ $user->created_at->format('F j, Y') }}</p>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">{{ __('Last Updated') }}</label>
                                                <p class="form-control-plaintext">{{ $user->updated_at->format('F j, Y') }}</p>
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
                                        <i class="fas fa-chart-bar"></i> {{ __('Quick Stats') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <div class="h4 text-primary">{{ $user->attendances->count() }}</div>
                                        <small class="text-muted">{{ __('Total Attendance Records') }}</small>
                                    </div>
                                    
                                    <div class="text-center mb-3">
                                        <div class="h4 text-success">{{ $user->attendances->where('status', 'Full Day')->count() }}</div>
                                        <small class="text-muted">{{ __('Full Days') }}</small>
                                    </div>
                                    
                                    <div class="text-center">
                                        <div class="h4 text-warning">{{ $user->attendances->where('status', 'Half Day')->count() }}</div>
                                        <small class="text-muted">{{ __('Half Days') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Attendance Records -->
                    @if($user->attendances->count() > 0)
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-clock"></i> {{ __('Recent Attendance Records') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>{{ __('Date') }}</th>
                                                    <th>{{ __('Check In') }}</th>
                                                    <th>{{ __('Check Out') }}</th>
                                                    <th>{{ __('Worked Hours') }}</th>
                                                    <th>{{ __('Status') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($user->attendances as $attendance)
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
                            </div>
                        </div>
                    </div>
                    @endif

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
</x-app-layout> 