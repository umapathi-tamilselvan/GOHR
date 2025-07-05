<x-modal name="edit-user-modal-{{ $user->id }}" :show="$errors->userDeletion->isNotEmpty()" focusable>
    <form method="post" action="{{ route('users.update', $user) }}" class="p-6">
        @csrf
        @method('patch')

        <x-slot name="title">
            {{ __('Edit User') }}
        </x-slot>

        <x-slot name="content">
            <div class="mt-6">
                <x-input-label for="name-{{ $user->id }}" value="{{ __('Name') }}" />
                <x-text-input id="name-{{ $user->id }}" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus />
                <x-input-error :messages="$errors->userDeletion->get('name')" class="mt-2" />
            </div>
            
            <div class="mt-6">
                <x-input-label for="email-{{ $user->id }}" value="{{ __('Email') }}" />
                <x-text-input id="email-{{ $user->id }}" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required />
                <x-input-error :messages="$errors->userDeletion->get('email')" class="mt-2" />
            </div>

            @if (auth()->user()->hasRole('Super Admin'))
                <div class="mt-6">
                    <x-input-label for="organization_id-{{ $user->id }}" value="{{ __('Organization') }}" />
                    <select name="organization_id" id="organization_id-{{ $user->id }}" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        @foreach ($organizations as $organization)
                            <option value="{{ $organization->id }}" @selected($user->organization_id == $organization->id)>{{ $organization->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->userDeletion->get('organization_id')" class="mt-2" />
                </div>
            @endif

            <div class="mt-6">
                <x-input-label for="role-{{ $user->id }}" value="{{ __('Role') }}" />
                <select name="role" id="role-{{ $user->id }}" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}" @selected($user->hasRole($role->name))>{{ $role->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->userDeletion->get('role')" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button x-on:click="$dispatch('close')">
                {{ __('Cancel') }}
            </x-secondary-button>

            <x-primary-button class="ms-3">
                {{ __('Save Changes') }}
            </x-primary-button>
        </x-slot>
    </form>
</x-modal> 