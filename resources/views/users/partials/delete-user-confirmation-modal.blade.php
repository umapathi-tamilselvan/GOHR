<x-modal name="confirm-user-deletion-{{ $user->id }}" focusable>
    <form method="post" action="{{ route('users.destroy', $user) }}" class="p-6">
        @csrf
        @method('delete')

        <x-slot name="title">
            {{ __('Are you sure you want to delete this user?') }}
        </x-slot>

        <x-slot name="content">
            <p class="mt-1 text-sm text-gray-600">
                {{ __('Once the user is deleted, all of their resources and data will be permanently deleted.') }}
            </p>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button x-on:click="$dispatch('close')">
                {{ __('Cancel') }}
            </x-secondary-button>

            <x-danger-button class="ms-3">
                {{ __('Delete User') }}
            </x-danger-button>
        </x-slot>
    </form>
</x-modal> 