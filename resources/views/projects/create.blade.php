<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add New Project') }}
        </h2>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <form action="{{ route('projects.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700">Project Name</label>
                    <input type="text" name="name" id="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                </div>

                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                </div>

                <div class="mb-4">
                    <label for="manager_id" class="block text-sm font-medium text-gray-700">Manager</label>
                    <select name="manager_id" id="manager_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">Select a Manager</option>
                        @foreach($managers as $manager)
                            <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label for="users" class="block text-sm font-medium text-gray-700">Assign Users</label>
                    <select name="users[]" id="users" multiple class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-{{ $roleColor }}-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-{{ $roleColor }}-600">
                        {{ __('Save Project') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout> 