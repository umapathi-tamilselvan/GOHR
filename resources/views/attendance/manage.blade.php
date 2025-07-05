<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Attendance') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('attendances.storeManual') }}" method="POST">
                        @csrf

                        <!-- Employee -->
                        <div class="mb-4">
                            <label for="user_id" class="block font-medium text-sm text-gray-700">{{ __('Employee') }}</label>
                            <select name="user_id" id="user_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                <option value="">{{ __('Select Employee') }}</option>
                                @foreach ($organizationUsers as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Date -->
                        <div class="mb-4">
                            <label for="date" class="block font-medium text-sm text-gray-700">{{ __('Date') }}</label>
                            <input type="date" name="date" id="date" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                        </div>

                        <!-- Check-in Time -->
                        <div class="mb-4">
                            <label for="check_in" class="block font-medium text-sm text-gray-700">{{ __('Check-in Time') }}</label>
                            <input type="time" name="check_in" id="check_in" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                        </div>

                        <!-- Check-out Time -->
                        <div class="mb-4">
                            <label for="check_out" class="block font-medium text-sm text-gray-700">{{ __('Check-out Time') }}</label>
                            <input type="time" name="check_out" id="check_out" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('Save Attendance') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 