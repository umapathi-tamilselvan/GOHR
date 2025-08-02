<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit User') }}
            </h2>
            <div>
                <a href="{{ route('users.show', $user) }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-eye"></i> {{ __('View User') }}
                </a>
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
                    
                    <div class="row justify-content-center">
                        <div class="col-12 col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-edit"></i> {{ __('Edit User Information') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('users.update', $user) }}" class="needs-validation" novalidate>
                                        @csrf
                                        @method('PUT')
                                        
                                        <div class="row">
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3">
                                                    <x-input-label for="name" :value="__('Name')" />
                                                    <x-text-input id="name" class="form-control form-control-sm @error('name', 'userDeletion') is-invalid @enderror" 
                                                        type="text" name="name" :value="old('name', $user->name)" required autofocus />
                                                    <x-input-error :messages="$errors->get('name', 'userDeletion')" class="invalid-feedback" />
                                                </div>
                                            </div>
                                            
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3">
                                                    <x-input-label for="email" :value="__('Email')" />
                                                    <x-text-input id="email" class="form-control form-control-sm @error('email', 'userDeletion') is-invalid @enderror" 
                                                        type="email" name="email" :value="old('email', $user->email)" required />
                                                    <x-input-error :messages="$errors->get('email', 'userDeletion')" class="invalid-feedback" />
                                                </div>
                                            </div>
                                        </div>

                                        @if(auth()->user()->hasRole('Super Admin'))
                                        <div class="row">
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3">
                                                    <x-input-label for="organization_id" :value="__('Organization')" />
                                                    <select id="organization_id" name="organization_id" class="form-select form-select-sm @error('organization_id', 'userDeletion') is-invalid @enderror" required>
                                                        <option value="">{{ __('Select Organization') }}</option>
                                                        @foreach($organizations as $organization)
                                                            <option value="{{ $organization->id }}" {{ old('organization_id', $user->organization_id) == $organization->id ? 'selected' : '' }}>
                                                                {{ $organization->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <x-input-error :messages="$errors->get('organization_id', 'userDeletion')" class="invalid-feedback" />
                                                </div>
                                            </div>
                                            
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3">
                                                    <x-input-label for="role" :value="__('Role')" />
                                                    <select id="role" name="role" class="form-select form-select-sm @error('role', 'userDeletion') is-invalid @enderror" required>
                                                        <option value="">{{ __('Select Role') }}</option>
                                                        @foreach($roles as $role)
                                                            <option value="{{ $role->name }}" {{ old('role', $user->roles->first()->name ?? '') == $role->name ? 'selected' : '' }}>
                                                                {{ $role->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <x-input-error :messages="$errors->get('role', 'userDeletion')" class="invalid-feedback" />
                                                </div>
                                            </div>
                                        </div>
                                        @else
                                        <div class="row">
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3">
                                                    <x-input-label for="organization_id" :value="__('Organization')" />
                                                    <input type="text" class="form-control form-control-sm" value="{{ $user->organization->name ?? __('Not Assigned') }}" readonly />
                                                    <input type="hidden" name="organization_id" value="{{ $user->organization_id }}" />
                                                </div>
                                            </div>
                                            
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3">
                                                    <x-input-label for="role" :value="__('Role')" />
                                                    <select id="role" name="role" class="form-select form-select-sm @error('role', 'userDeletion') is-invalid @enderror" required>
                                                        <option value="">{{ __('Select Role') }}</option>
                                                        @foreach($roles as $role)
                                                            <option value="{{ $role->name }}" {{ old('role', $user->roles->first()->name ?? '') == $role->name ? 'selected' : '' }}>
                                                                {{ $role->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <x-input-error :messages="$errors->get('role', 'userDeletion')" class="invalid-feedback" />
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <div class="d-flex justify-content-between">
                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-save"></i> {{ __('Update User') }}
                                                    </button>
                                                    <a href="{{ route('users.show', $user) }}" class="btn btn-secondary btn-sm">
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