<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Employee Details') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('employees.index') }}" class="inline-flex items-center px-3 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Employees
                    </a>
                    <h1 class="text-2xl font-semibold text-gray-900">{{ $employee->user->name }}</h1>
                </div>
                <div class="flex space-x-3">
                    @can('update', $employee)
                        <a href="{{ route('employees.edit', $employee) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit Employee
                        </a>
                    @endcan
                    @can('delete', $employee)
                        <button onclick="confirmDelete({{ $employee->id }}, '{{ $employee->user->name }}')" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete Employee
                        </button>
                    @endcan
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Employee Information -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Information Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center space-x-4 mb-6">
                                <div class="h-16 w-16 rounded-full bg-blue-100 flex items-center justify-center">
                                    <span class="text-2xl font-bold text-blue-600">
                                        {{ strtoupper(substr($employee->user->name, 0, 1)) }}
                                    </span>
                                </div>
                                <div>
                                    <h3 class="text-xl font-semibold text-gray-900">{{ $employee->user->name }}</h3>
                                    <p class="text-gray-600">{{ $employee->user->email }}</p>
                                    <p class="text-sm text-gray-500">Employee ID: {{ $employee->employee_id }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h4 class="text-lg font-medium text-gray-900 mb-4">Employment Details</h4>
                                    <dl class="space-y-3">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Department</dt>
                                            <dd class="text-sm text-gray-900">{{ $employee->department }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Position</dt>
                                            <dd class="text-sm text-gray-900">{{ $employee->position }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Hire Date</dt>
                                            <dd class="text-sm text-gray-900">{{ $employee->hire_date ? $employee->hire_date->format('M d, Y') : 'N/A' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Years of Service</dt>
                                            <dd class="text-sm text-gray-900">{{ $employee->years_of_service }} years</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Manager</dt>
                                            <dd class="text-sm text-gray-900">{{ $employee->manager ? $employee->manager->name : 'No Manager Assigned' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Organization</dt>
                                            <dd class="text-sm text-gray-900">{{ $employee->organization->name }}</dd>
                                        </div>
                                    </dl>
                                </div>

                                <div>
                                    <h4 class="text-lg font-medium text-gray-900 mb-4">Status & Compensation</h4>
                                    <dl class="space-y-3">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                                            <dd>
                                                @php
                                                    $statusClasses = [
                                                        'active' => 'bg-green-100 text-green-800',
                                                        'probation' => 'bg-yellow-100 text-yellow-800',
                                                        'on_leave' => 'bg-blue-100 text-blue-800',
                                                        'terminated' => 'bg-red-100 text-red-800'
                                                    ];
                                                    $statusClass = $statusClasses[$employee->status] ?? 'bg-gray-100 text-gray-800';
                                                @endphp
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClass }}">
                                                    {{ ucfirst(str_replace('_', ' ', $employee->status)) }}
                                                </span>
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Salary</dt>
                                            <dd class="text-sm text-gray-900">{{ $employee->salary ? '$' . number_format($employee->salary, 2) : 'Not specified' }}</dd>
                                        </div>
                                        @if($employee->termination_date)
                                            <div>
                                                <dt class="text-sm font-medium text-gray-500">Termination Date</dt>
                                                <dd class="text-sm text-gray-900">{{ $employee->termination_date->format('M d, Y') }}</dd>
                                            </div>
                                        @endif
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Information -->
                    @if($employee->profile)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <dl class="space-y-3">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Date of Birth</dt>
                                            <dd class="text-sm text-gray-900">{{ $employee->profile->date_of_birth ? $employee->profile->date_of_birth->format('M d, Y') : 'Not specified' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Age</dt>
                                            <dd class="text-sm text-gray-900">{{ $employee->profile->age ?? 'Not specified' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Gender</dt>
                                            <dd class="text-sm text-gray-900">{{ ucfirst($employee->profile->gender ?? 'Not specified') }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Marital Status</dt>
                                            <dd class="text-sm text-gray-900">{{ ucfirst($employee->profile->marital_status ?? 'Not specified') }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Nationality</dt>
                                            <dd class="text-sm text-gray-900">{{ $employee->profile->nationality ?? 'Not specified' }}</dd>
                                        </div>
                                    </dl>
                                    <dl class="space-y-3">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                            <dd class="text-sm text-gray-900">{{ is_string($employee->profile->phone) ? $employee->profile->phone : 'Not specified' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Address</dt>
                                            <dd class="text-sm text-gray-900">{{ is_string($employee->profile->address) ? $employee->profile->address : 'Not specified' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Emergency Contact</dt>
                                            <dd class="text-sm text-gray-900">
                                                @if($employee->profile->emergency_contact_name && is_string($employee->profile->emergency_contact_name))
                                                    {{ $employee->profile->emergency_contact_name }} 
                                                    @if($employee->profile->emergency_contact_relationship && is_string($employee->profile->emergency_contact_relationship))
                                                        ({{ $employee->profile->emergency_contact_relationship }})
                                                    @endif
                                                    <br>
                                                    @if($employee->profile->emergency_contact_phone && is_string($employee->profile->emergency_contact_phone))
                                                        <span class="text-gray-500">{{ $employee->profile->emergency_contact_phone }}</span>
                                                    @endif
                                                @else
                                                    Not specified
                                                @endif
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <!-- Skills & Certifications -->
                        @if($employee->profile->skills || $employee->profile->certifications)
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="p-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Skills & Certifications</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        @if($employee->profile->skills && is_array($employee->profile->skills) && count($employee->profile->skills) > 0)
                                            <div>
                                                <h4 class="text-md font-medium text-gray-700 mb-2">Skills</h4>
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach($employee->profile->skills as $skill)
                                                        @if(is_string($skill))
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                                {{ $skill }}
                                                            </span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                        @if($employee->profile->certifications && is_array($employee->profile->certifications) && count($employee->profile->certifications) > 0)
                                            <div>
                                                <h4 class="text-md font-medium text-gray-700 mb-2">Certifications</h4>
                                                <div class="space-y-2">
                                                    @foreach($employee->profile->certifications as $cert)
                                                        @if(is_array($cert) && isset($cert['name']))
                                                            <div class="text-sm text-gray-900">{{ $cert['name'] }}</div>
                                                        @elseif(is_string($cert))
                                                            <div class="text-sm text-gray-900">{{ $cert }}</div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                    <!-- Documents -->
                    @if($employee->documents->count() > 0)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Documents</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Document</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($employee->documents as $document)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ is_string($document->title) ? $document->title : 'Untitled' }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ is_string($document->document_type) ? ucfirst(str_replace('_', ' ', $document->document_type)) : 'Unknown' }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $document->file_size_human ?? 'Unknown' }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        @php
                                                            $statusClasses = [
                                                                'active' => 'bg-green-100 text-green-800',
                                                                'expired' => 'bg-red-100 text-red-800',
                                                                'archived' => 'bg-gray-100 text-gray-800'
                                                            ];
                                                            $statusClass = $statusClasses[$document->status] ?? 'bg-gray-100 text-gray-800';
                                                        @endphp
                                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClass }}">
                                                            {{ is_string($document->status) ? ucfirst($document->status) : 'Unknown' }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {{ $document->expiry_date && method_exists($document->expiry_date, 'format') ? $document->expiry_date->format('M d, Y') : 'No expiry' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Leave Balances -->
                    @if($leaveBalances->count() > 0)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Leave Balances</h3>
                                <div class="space-y-3">
                                    @foreach($leaveBalances as $balance)
                                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $balance->leaveType->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $balance->year }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm font-semibold text-gray-900">{{ $balance->remaining_days }}</p>
                                                <p class="text-xs text-gray-500">of {{ $balance->total_days }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Recent Activity -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Activity</h3>
                            <div class="space-y-3">
                                @if($employee->attendances->count() > 0)
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <div class="h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Attendance Record</p>
                                            <p class="text-xs text-gray-500">{{ $employee->attendances->count() }} records</p>
                                        </div>
                                    </div>
                                @endif
                                @if($employee->leaves->count() > 0)
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <div class="h-8 w-8 bg-green-100 rounded-full flex items-center justify-center">
                                                <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Leave Requests</p>
                                            <p class="text-xs text-gray-500">{{ $employee->leaves->count() }} requests</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                            <div class="space-y-2">
                                <a href="{{ route('attendances.create') }}?user_id={{ $employee->user_id }}" class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Record Attendance
                                </a>
                                <a href="{{ route('leaves.create') }}?user_id={{ $employee->user_id }}" class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    Apply Leave
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" id="deleteModal">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Delete Employee</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">
                        Are you sure you want to delete <span id="employeeName" class="font-medium text-gray-900"></span>? This action cannot be undone.
                    </p>
                </div>
                <div class="items-center px-4 py-3">
                    <form id="deleteForm" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-24 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@push('scripts')
<script>
function confirmDelete(employeeId, employeeName) {
    document.getElementById('employeeName').textContent = employeeName;
    document.getElementById('deleteForm').action = `/employees/${employeeId}`;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});
</script>
@endpush 