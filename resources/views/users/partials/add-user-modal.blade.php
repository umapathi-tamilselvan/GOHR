<x-modal name="add-user-modal" :show="$errors->userCreation->isNotEmpty()" focusable>
    <form method="post" action="{{ route('users.store') }}" class="p-6">
        @csrf

        <x-slot name="title">
            {{ __('Add New User') }}
        </x-slot>

        <x-slot name="content">
            <div class="mt-6">
                <x-input-label for="name" value="{{ __('Name') }}" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                <x-input-error :messages="$errors->userCreation->get('name')" class="mt-2" />
            </div>
            
            <div class="mt-6">
                <x-input-label for="email" value="{{ __('Email') }}" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
                <x-input-error :messages="$errors->userCreation->get('email')" class="mt-2" />
            </div>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" />
                <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required />
                <x-input-error :messages="$errors->userCreation->get('password')" class="mt-2" />
            </div>

            <div class="mt-6">
                <x-input-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required />
                <x-input-error :messages="$errors->userCreation->get('password_confirmation')" class="mt-2" />
            </div>

            @if (auth()->user()->hasRole('Super Admin'))
                <div class="mt-6">
                    <x-input-label for="organization_id" value="{{ __('Organization') }}" />
                    <select name="organization_id" id="organization_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        @foreach ($organizations as $organization)
                            <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->userCreation->get('organization_id')" class="mt-2" />
                </div>
            @endif

            <div class="mt-6">
                <x-input-label for="role" value="{{ __('Role') }}" />
                <select name="role" id="role" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->userCreation->get('role')" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button x-on:click="$dispatch('close')">
                {{ __('Cancel') }}
            </x-secondary-button>

            <x-primary-button class="ms-3">
                {{ __('Add User') }}
            </x-primary-button>
        </x-slot>
    </form>
</x-modal> 