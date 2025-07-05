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
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold">{{ __('Total Organizations') }}</h4>
                                <p class="text-2xl font-bold text-gray-800">{{ $data['total_organizations'] }}</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold">{{ __('Total Users') }}</h4>
                                <p class="text-2xl font-bold text-gray-800">{{ $data['total_users'] }}</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold">{{ __('Pending Leave Requests') }}</h4>
                                <p class="text-2xl font-bold text-gray-800">{{ $data['pending_leave_requests'] }}</p>
                                @if($data['pending_leave_requests'] > 0)
                                    <a href="{{ route('leave-requests.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">View Requests</a>
                                @endif
                            </div>
                        </div>
                    @elseif (auth()->user()->hasRole('HR'))
                        <h3 class="text-lg font-semibold">{{ __('HR Dashboard') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold">{{ __('Total Employees') }}</h4>
                                <p class="text-2xl font-bold text-gray-800">{{ $data['total_employees'] }}</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold">{{ __('Pending Manager Leave Requests') }}</h4>
                                <p class="text-2xl font-bold text-gray-800">{{ $data['pending_leave_requests'] }}</p>
                                @if($data['pending_leave_requests'] > 0)
                                    <a href="{{ route('leave-requests.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">Review Requests</a>
                                @endif
                            </div>
                        </div>
                    @elseif (auth()->user()->hasRole('Manager'))
                        <h3 class="text-lg font-semibold">{{ __('Manager Dashboard') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold">{{ __('Total Employees') }}</h4>
                                <p class="text-2xl font-bold text-gray-800">{{ $data['total_employees'] }}</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold">{{ __('Pending Employee Leave Requests') }}</h4>
                                <p class="text-2xl font-bold text-gray-800">{{ $data['pending_leave_requests'] }}</p>
                                @if($data['pending_leave_requests'] > 0)
                                    <a href="{{ route('leave-requests.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">Review Requests</a>
                                @endif
                            </div>
                        </div>
                    @else
                        <h3 class="text-lg font-semibold">{{ __('Employee Dashboard') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold">{{ __('Days Present This Month') }}</h4>
                                <p class="text-2xl font-bold text-gray-800">{{ $data['monthly_attendance'] }}</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold">{{ __('My Leave Requests') }}</h4>
                                <p class="text-2xl font-bold text-gray-800">{{ $data['my_leave_requests'] }}</p>
                                <a href="{{ route('leave-requests.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">View My Requests</a>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold">{{ __('Pending Requests') }}</h4>
                                <p class="text-2xl font-bold text-gray-800">{{ $data['pending_my_requests'] }}</p>
                                @if($data['pending_my_requests'] > 0)
                                    <a href="{{ route('leave-requests.create') }}" class="text-blue-600 hover:text-blue-800 text-sm">Apply for Leave</a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
