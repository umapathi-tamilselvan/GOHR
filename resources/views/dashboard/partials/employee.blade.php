<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Days Present Card -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex items-center">
            <div class="bg-green-500 p-3 rounded-full text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Days Present (This Month)</p>
                <p class="text-2xl font-bold text-gray-800">{{ $data['attendance_stats']['present_days'] }}</p>
            </div>
        </div>
    </div>

    <!-- Total Worked Hours Card -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex items-center">
            <div class="bg-blue-500 p-3 rounded-full text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Worked Hours (This Month)</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($data['total_worked_hours'], 1) }}</p>
            </div>
        </div>
    </div>

    <!-- Today's Status Card -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex items-center">
            <div class="bg-purple-500 p-3 rounded-full text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Today's Status</p>
                @if($data['today_attendance'])
                    @if($data['today_attendance']->status == 'Full Day')
                        <p class="text-2xl font-bold text-green-600">{{ $data['today_attendance']->status }}</p>
                    @elseif($data['today_attendance']->status == 'Half Day')
                        <p class="text-2xl font-bold text-yellow-600">{{ $data['today_attendance']->status }}</p>
                    @else
                        <p class="text-2xl font-bold text-red-600">{{ $data['today_attendance']->status }}</p>
                    @endif
                @else
                    <p class="text-2xl font-bold text-gray-400">Not Marked</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Attendance Rate Card -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex items-center">
            <div class="bg-orange-500 p-3 rounded-full text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Attendance Rate</p>
                @php
                    $totalDays = $data['attendance_stats']['present_days'] + $data['attendance_stats']['absent_days'];
                    $attendanceRate = $totalDays > 0 ? round(($data['attendance_stats']['present_days'] / $totalDays) * 100, 1) : 0;
                @endphp
                <p class="text-2xl font-bold text-gray-800">{{ $attendanceRate }}%</p>
            </div>
        </div>
    </div>
</div>

<!-- Today's Attendance and Weekly Summary -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Today's Attendance Details -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Today's Attendance</h3>
        @if($data['today_attendance'])
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Status</p>
                        <p class="text-lg font-semibold text-gray-800">
                            @if($data['today_attendance']->status == 'Full Day')
                                <span class="text-green-600">{{ $data['today_attendance']->status }}</span>
                            @elseif($data['today_attendance']->status == 'Half Day')
                                <span class="text-yellow-600">{{ $data['today_attendance']->status }}</span>
                            @else
                                <span class="text-red-600">{{ $data['today_attendance']->status }}</span>
                            @endif
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-500">Worked Hours</p>
                        <p class="text-lg font-semibold text-gray-800">{{ number_format($data['today_attendance']->worked_minutes / 60, 1) }}h</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-blue-50 rounded-lg">
                        <p class="text-sm font-medium text-gray-500">Check-in</p>
                        <p class="text-lg font-semibold text-gray-800">
                            {{ $data['today_attendance']->check_in ? \Carbon\Carbon::parse($data['today_attendance']->check_in)->format('h:i A') : 'N/A' }}
                        </p>
                    </div>
                    <div class="p-4 bg-green-50 rounded-lg">
                        <p class="text-sm font-medium text-gray-500">Check-out</p>
                        <p class="text-lg font-semibold text-gray-800">
                            {{ $data['today_attendance']->check_out ? \Carbon\Carbon::parse($data['today_attendance']->check_out)->format('h:i A') : 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p class="text-gray-500">No attendance marked for today</p>
                <a href="{{ route('attendances.index') }}" class="mt-2 inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 transition ease-in-out duration-150">
                    Mark Attendance
                </a>
            </div>
        @endif
    </div>

    <!-- Weekly Summary -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Weekly Summary</h3>
        <div class="space-y-3">
            @forelse($data['weekly_summary'] as $day)
                @php
                    $date = \Carbon\Carbon::parse($day->date);
                    $isToday = $date->isToday();
                    $isWeekend = $date->isWeekend();
                @endphp
                <div class="flex items-center justify-between p-3 {{ $isToday ? 'bg-blue-50 border border-blue-200' : 'bg-gray-50' }} rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            @if($isToday)
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-medium text-blue-600">T</span>
                                </div>
                            @elseif($isWeekend)
                                <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-medium text-gray-400">W</span>
                                </div>
                            @else
                                <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-medium text-gray-600">{{ $date->format('d') }}</span>
                                </div>
                            @endif
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">{{ $date->format('D, M d') }}</p>
                            @if($isWeekend)
                                <p class="text-xs text-gray-400">Weekend</p>
                            @else
                                <p class="text-xs text-gray-500">{{ $day->present > 0 ? 'Present' : 'Absent' }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        @if($isWeekend)
                            <span class="text-xs text-gray-400">Weekend</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $day->present > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $day->present > 0 ? 'Present' : 'Absent' }}
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center py-4">No attendance data for this week</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Attendance Statistics and Recent Attendance -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Attendance Statistics -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">This Month's Statistics</h3>
        <div class="grid grid-cols-2 gap-4">
            <div class="p-4 bg-green-50 rounded-lg text-center">
                <p class="text-2xl font-bold text-green-600">{{ $data['attendance_stats']['full_days'] }}</p>
                <p class="text-sm text-gray-600">Full Days</p>
            </div>
            <div class="p-4 bg-yellow-50 rounded-lg text-center">
                <p class="text-2xl font-bold text-yellow-600">{{ $data['attendance_stats']['half_days'] }}</p>
                <p class="text-sm text-gray-600">Half Days</p>
            </div>
            <div class="p-4 bg-red-50 rounded-lg text-center">
                <p class="text-2xl font-bold text-red-600">{{ $data['attendance_stats']['absent_days'] }}</p>
                <p class="text-sm text-gray-600">Absent Days</p>
            </div>
            <div class="p-4 bg-blue-50 rounded-lg text-center">
                <p class="text-2xl font-bold text-blue-600">{{ number_format($data['total_worked_hours'], 1) }}</p>
                <p class="text-sm text-gray-600">Total Hours</p>
            </div>
        </div>
        
        <!-- Attendance Rate Progress -->
        <div class="mt-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700">Attendance Rate</span>
                <span class="text-sm font-medium text-gray-700">{{ $attendanceRate }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $attendanceRate }}%"></div>
            </div>
        </div>
    </div>

    <!-- Recent Attendance -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Attendance (Last 7 Days)</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-out</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($data['recent_attendance'] as $attendance)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ \Carbon\Carbon::parse($attendance->date)->format('d M, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($attendance->status == 'Full Day')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $attendance->status }}
                                    </span>
                                @elseif($attendance->status == 'Half Day')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        {{ $attendance->status }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        {{ $attendance->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('h:i A') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('h:i A') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($attendance->worked_minutes / 60, 1) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">No recent attendance records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="mt-8 bg-white p-6 rounded-lg shadow-md">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
    <div class="flex flex-wrap gap-4">
        <a href="{{ route('attendances.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 transition ease-in-out duration-150">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Mark Attendance
        </a>
        <a href="{{ route('attendances.list') }}" class="inline-flex items-center px-4 py-2 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 transition ease-in-out duration-150">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            View History
        </a>
        <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-purple-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-600 transition ease-in-out duration-150">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            Update Profile
        </a>
    </div>
</div> 