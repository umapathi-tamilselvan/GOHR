<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Attendance') }}
            </h2>
            <div>
                <a href="{{ route('attendances.show', $attendance) }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-eye"></i> {{ __('View Attendance') }}
                </a>
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
                    
                    <div class="row justify-content-center">
                        <div class="col-12 col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-edit"></i> {{ __('Edit Attendance Record') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('attendances.update', $attendance) }}" class="needs-validation" novalidate>
                                        @csrf
                                        @method('PUT')
                                        
                                        <div class="row">
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3">
                                                    <x-input-label for="user_id" :value="__('Employee')" />
                                                    <select id="user_id" name="user_id" class="form-select form-select-sm @error('user_id') is-invalid @enderror" required>
                                                        <option value="">{{ __('Select Employee') }}</option>
                                                        @foreach($organizationUsers as $user)
                                                            <option value="{{ $user->id }}" {{ old('user_id', $attendance->user_id) == $user->id ? 'selected' : '' }}>
                                                                {{ $user->name }} ({{ $user->email }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <x-input-error :messages="$errors->get('user_id')" class="invalid-feedback" />
                                                </div>
                                            </div>
                                            
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3">
                                                    <x-input-label for="date" :value="__('Date')" />
                                                    <x-text-input id="date" class="form-control form-control-sm @error('date') is-invalid @enderror" 
                                                        type="date" name="date" :value="old('date', $attendance->date->format('Y-m-d'))" required />
                                                    <x-input-error :messages="$errors->get('date')" class="invalid-feedback" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3">
                                                    <x-input-label for="check_in" :value="__('Check In Time')" />
                                                    <x-text-input id="check_in" class="form-control form-control-sm @error('check_in') is-invalid @enderror" 
                                                        type="time" name="check_in" :value="old('check_in', $attendance->check_in ? $attendance->check_in->format('H:i') : '')" required />
                                                    <x-input-error :messages="$errors->get('check_in')" class="invalid-feedback" />
                                                </div>
                                            </div>
                                            
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3">
                                                    <x-input-label for="check_out" :value="__('Check Out Time')" />
                                                    <x-text-input id="check_out" class="form-control form-control-sm @error('check_out') is-invalid @enderror" 
                                                        type="time" name="check_out" :value="old('check_out', $attendance->check_out ? $attendance->check_out->format('H:i') : '')" />
                                                    <x-input-error :messages="$errors->get('check_out')" class="invalid-feedback" />
                                                    <small class="form-text text-muted">{{ __('Leave empty if not checked out yet') }}</small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Current Status Display -->
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="alert alert-info">
                                                    <div class="row">
                                                        <div class="col-12 col-md-4">
                                                            <strong>{{ __('Current Status') }}:</strong>
                                                            @if($attendance->status === 'Full Day')
                                                                <span class="badge bg-success">{{ $attendance->status }}</span>
                                                            @elseif($attendance->status === 'Half Day')
                                                                <span class="badge bg-warning">{{ $attendance->status }}</span>
                                                            @else
                                                                <span class="badge bg-danger">{{ $attendance->status }}</span>
                                                            @endif
                                                        </div>
                                                        <div class="col-12 col-md-4">
                                                            <strong>{{ __('Worked Hours') }}:</strong>
                                                            @if($attendance->worked_minutes)
                                                                {{ number_format($attendance->worked_minutes / 60, 1) }} {{ __('hours') }}
                                                            @else
                                                                {{ __('No hours') }}
                                                            @endif
                                                        </div>
                                                        <div class="col-12 col-md-4">
                                                            <strong>{{ __('Last Updated') }}:</strong>
                                                            {{ $attendance->updated_at->format('M j, Y H:i') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <div class="d-flex justify-content-between">
                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-save"></i> {{ __('Update Attendance') }}
                                                    </button>
                                                    <a href="{{ route('attendances.show', $attendance) }}" class="btn btn-secondary btn-sm">
                                                        <i class="fas fa-times"></i> {{ __('Cancel') }}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout> 