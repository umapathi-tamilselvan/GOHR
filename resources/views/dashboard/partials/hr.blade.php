<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Employees Card -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex items-center">
            <div class="bg-green-500 p-3 rounded-full text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.653-.184-1.268-.5-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.653.184-1.268.5-1.857m0 0a5.002 5.002 0 019 0m-4.5 4.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5zM12 12a5 5 0 100-10 5 5 0 000 10z"></path></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Employees</p>
                <p class="text-2xl font-bold text-gray-800">{{ $data['employees_count'] }}</p>
            </div>
        </div>
    </div>

    <!-- Today's Present Card -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex items-center">
            <div class="bg-blue-500 p-3 rounded-full text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Present Today</p>
                <p class="text-2xl font-bold text-gray-800">{{ $data['today_present'] }}</p>
            </div>
        </div>
    </div>

    <!-- Today's Absent Card -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex items-center">
            <div class="bg-red-500 p-3 rounded-full text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Absent Today</p>
                <p class="text-2xl font-bold text-gray-800">{{ $data['today_absent'] }}</p>
            </div>
        </div>
    </div>

    <!-- Attendance Rate Card -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex items-center">
            <div class="bg-purple-500 p-3 rounded-full text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Attendance Rate</p>
                <p class="text-2xl font-bold text-gray-800">
                    {{ $data['employees_count'] > 0 ? round(($data['today_present'] / $data['employees_count']) * 100, 1) : 0 }}%
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Weekly Attendance Summary and Recent Issues -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Weekly Attendance Summary -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Weekly Attendance Summary</h3>
        <div class="space-y-3">
            @forelse($data['weekly_summary'] as $day)
                @php
                    $date = \Carbon\Carbon::parse($day->date);
                    $presentRate = $day->total > 0 ? round(($day->present / $day->total) * 100, 1) : 0;
                @endphp
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-800">{{ $date->format('D, M d') }}</p>
                        <p class="text-sm text-gray-500">{{ $day->present }}/{{ $day->total }} present</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-800">{{ $presentRate }}%</p>
                        <div class="w-16 bg-gray-200 rounded-full h-2 mt-1">
                            <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $presentRate }}%"></div>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center py-4">No attendance data for this week</p>
            @endforelse
        </div>
    </div>

    <!-- Recent Attendance Issues -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Attendance Issues</h3>
        <div class="space-y-3 max-h-64 overflow-y-auto">
            @forelse($data['recent_issues'] as $issue)
                <div class="flex items-center justify-between p-3 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $issue->user->name }}</p>
                            <p class="text-sm text-gray-600">{{ $issue->date->format('M d, Y') }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            {{ $issue->status }}
                        </span>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center py-4">No recent attendance issues</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Employee List and Quick Actions -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Employee List -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Employee Overview</h3>
        <div class="space-y-3 max-h-64 overflow-y-auto">
            @forelse($data['employees']->take(8) as $employee)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                <span class="text-sm font-medium text-gray-700">{{ substr($employee->name, 0, 1) }}</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $employee->name }}</p>
                            <p class="text-xs text-gray-500">{{ $employee->email }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        @foreach($employee->roles as $role)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $role->name }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center py-4">No employees found</p>
            @endforelse
        </div>
        @if($data['employees']->count() > 8)
            <div class="mt-4 text-center">
                <a href="{{ route('users.index') }}" class="text-sm text-blue-600 hover:text-blue-800">View all employees →</a>
            </div>
        @endif
    </div>

    <!-- Quick Actions -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
        <div class="space-y-3">
            @can('create', App\Models\User::class)
                <button
                    x-data=""
                    x-on:click.prevent="$dispatch('open-modal', 'add-user-modal')"
                    class="w-full flex items-center justify-center px-4 py-3 bg-blue-500 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-blue-600 transition ease-in-out duration-150"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Employee
                </button>
            @endcan
            
            @can('viewAny', App\Models\Attendance::class)
                <a href="{{ route('attendances.manage') }}" class="w-full flex items-center justify-center px-4 py-3 bg-green-500 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-green-600 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Manage Attendance
                </a>
                
                <a href="{{ route('attendances.report') }}" class="w-full flex items-center justify-center px-4 py-3 bg-purple-500 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-purple-600 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    View Reports
                </a>
            @endcan
            
            <a href="{{ route('users.index') }}" class="w-full flex items-center justify-center px-4 py-3 bg-gray-500 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-gray-600 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.653-.184-1.268-.5-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.653.184-1.268.5-1.857m0 0a5.002 5.002 0 019 0m-4.5 4.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5zM12 12a5 5 0 100-10 5 5 0 000 10z"></path>
                </svg>
                Manage Employees
            </a>
        </div>
    </div>
</div> 