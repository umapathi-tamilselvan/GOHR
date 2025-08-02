<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Team Members Card -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex items-center">
            <div class="bg-orange-500 p-3 rounded-full text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.653-.184-1.268-.5-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.653.184-1.268.5-1.857m0 0a5.002 5.002 0 019 0m-4.5 4.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5zM12 12a5 5 0 100-10 5 5 0 000 10z"></path></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Team Members</p>
                <p class="text-2xl font-bold text-gray-800">{{ $data['team_count'] }}</p>
            </div>
        </div>
    </div>

    <!-- Present Today Card -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex items-center">
            <div class="bg-green-500 p-3 rounded-full text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Present Today</p>
                <p class="text-2xl font-bold text-gray-800">{{ $data['team_attendance_today']->where('status', '!=', 'Incomplete')->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Absent Today Card -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex items-center">
            <div class="bg-red-500 p-3 rounded-full text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Absent Today</p>
                <p class="text-2xl font-bold text-gray-800">{{ $data['team_attendance_today']->where('status', 'Incomplete')->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Team Performance Card -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex items-center">
            <div class="bg-blue-500 p-3 rounded-full text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Avg. Hours (Month)</p>
                <p class="text-2xl font-bold text-gray-800">
                    {{ $data['team_count'] > 0 ? round($data['team_performance']->sum('worked_minutes') / ($data['team_count'] * 60), 1) : 0 }}
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Team Performance Overview and Weekly Summary -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Team Performance Overview -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Team Performance This Month</h3>
        <div class="space-y-4">
            @php
                $teamMembers = $data['team_members'];
                $performanceData = $data['team_performance']->groupBy('user_id');
            @endphp
            
            @foreach($teamMembers->take(6) as $member)
                @php
                    $memberAttendance = $performanceData->get($member->id, collect());
                    $totalHours = $memberAttendance->sum('worked_minutes') / 60;
                    $presentDays = $memberAttendance->where('status', '!=', 'Incomplete')->count();
                    $totalDays = $memberAttendance->count();
                    $attendanceRate = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0;
                @endphp
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                <span class="text-sm font-medium text-gray-700">{{ substr($member->name, 0, 1) }}</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $member->name }}</p>
                            <p class="text-xs text-gray-500">{{ $presentDays }}/{{ $totalDays }} days present</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-800">{{ number_format($totalHours, 1) }}h</p>
                        <div class="w-16 bg-gray-200 rounded-full h-2 mt-1">
                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ $attendanceRate }}%"></div>
                        </div>
                    </div>
                </div>
            @endforeach
            
            @if($teamMembers->count() > 6)
                <div class="text-center">
                    <a href="{{ route('users.index') }}" class="text-sm text-blue-600 hover:text-blue-800">View all team members →</a>
                </div>
            @endif
        </div>
    </div>

    <!-- Weekly Team Summary -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Weekly Team Summary</h3>
        <div class="space-y-3">
            @forelse($data['weekly_team_summary'] as $day)
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
</div>

<!-- Team Attendance Today and Issues -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Team Attendance Today -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Team Attendance Today</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-out</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($data['team_attendance_today'] as $attendance)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                            <span class="text-sm font-medium text-gray-700">{{ substr($attendance->user->name, 0, 1) }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $attendance->user->name }}</div>
                                    </div>
                                </div>
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('h:i A') : 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('h:i A') : 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">No attendance records for today.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Team Issues -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Team Issues</h3>
        <div class="space-y-3 max-h-64 overflow-y-auto">
            @forelse($data['team_issues'] as $issue)
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
                <p class="text-gray-500 text-center py-4">No recent team issues</p>
            @endforelse
        </div>
    </div>
</div> 