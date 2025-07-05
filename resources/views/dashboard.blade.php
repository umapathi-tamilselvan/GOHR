<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (auth()->user()->hasRole('Super Admin'))
                        <h3 class="text-lg font-semibold">{{ __('Super Admin Dashboard') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold">{{ __('Total Organizations') }}</h4>
                                <p class="text-2xl font-bold text-gray-800">{{ $data['total_organizations'] }}</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold">{{ __('Total Users') }}</h4>
                                <p class="text-2xl font-bold text-gray-800">{{ $data['total_users'] }}</p>
                            </div>
                        </div>
                    @elseif (auth()->user()->hasRole('HR') || auth()->user()->hasRole('Manager'))
                        <h3 class="text-lg font-semibold">{{ __('HR/Manager Dashboard') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold">{{ __('Total Employees') }}</h4>
                                <p class="text-2xl font-bold text-gray-800">{{ $data['total_employees'] }}</p>
                            </div>
                        </div>
                    @else
                        <h3 class="text-lg font-semibold">{{ __('Employee Dashboard') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold">{{ __('Days Present This Month') }}</h4>
                                <p class="text-2xl font-bold text-gray-800">{{ $data['monthly_attendance'] }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
