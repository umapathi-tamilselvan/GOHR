<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Leave Requests') }}
            </h2>
            <a href="{{ route('leave-requests.create') }}" class="inline-flex items-center px-4 py-2 bg-{{ $roleColor }}-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-{{ $roleColor }}-600">
                {{ __('Apply for Leave') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Filter Section -->
                    <div class="mb-4 flex justify-between items-center">
                        <div class="flex items-center space-x-4">
                            <form action="{{ route('leave-requests.index') }}" method="GET" class="flex items-center space-x-2">
                                <select name="status" class="border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                                <button type="submit" class="inline-flex items-center px-3 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600">
                                    Filter
                                </button>
                            </form>
                        </div>
                        <div class="text-sm text-gray-600">
                            Showing {{ $leaveRequests->count() }} of {{ $leaveRequests->total() }} requests
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applicant</th>
                                    @if(auth()->user()->hasRole('Super Admin'))
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Organization</th>
                                    @endif
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leave Type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($leaveRequests as $leaveRequest)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $leaveRequest->user->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $leaveRequest->user->email }}</div>
                                            </div>
                                        </td>
                                        @if(auth()->user()->hasRole('Super Admin'))
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $leaveRequest->user->organization->name ?? 'N/A' }}
                                        </td>
                                        @endif
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $leaveRequest->leave_type }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $leaveRequest->start_date->format('M d, Y') }} to {{ $leaveRequest->end_date->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($leaveRequest->status == 'approved') bg-green-100 text-green-800 @elseif($leaveRequest->status == 'rejected') bg-red-100 text-red-800 @else bg-yellow-100 text-yellow-800 @endif">
                                                {{ ucfirst($leaveRequest->status) }}
                                            </span>
                                            @if($leaveRequest->status === 'pending')
                                                <div class="text-xs text-gray-500 mt-1">
                                                    Waiting for {{ $leaveRequest->getNextApproverRole() }} approval
                                                </div>
                                            @elseif($leaveRequest->status === 'approved' && $leaveRequest->approver)
                                                <div class="text-xs text-gray-500 mt-1">
                                                    Approved by {{ $leaveRequest->approver->name }} on {{ $leaveRequest->approved_at->format('M d, Y') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center space-x-2">
                                                <a href="{{ route('leave-requests.show', $leaveRequest) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                                
                                                @can('approve', $leaveRequest)
                                                    @if($leaveRequest->status === 'pending')
                                                        <form action="{{ route('leave-requests.approve', $leaveRequest) }}" method="POST" class="inline-block">
                                                            @csrf
                                                            <input type="hidden" name="status" value="approved">
                                                            <button type="submit" class="inline-flex items-center px-2 py-1 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 focus:outline-none focus:border-green-700 focus:ring focus:ring-green-200 active:text-green-800 active:bg-green-900 disabled:opacity-25 transition">
                                                                Approve
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('leave-requests.approve', $leaveRequest) }}" method="POST" class="inline-block">
                                                            @csrf
                                                            <input type="hidden" name="status" value="rejected">
                                                            <button type="submit" class="inline-flex items-center px-2 py-1 bg-red-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600 focus:outline-none focus:border-red-700 focus:ring focus:ring-red-200 active:text-red-800 active:bg-red-900 disabled:opacity-25 transition">
                                                                Reject
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="text-gray-500 text-xs">Already {{ $leaveRequest->status }}</span>
                                                    @endif
                                                @else
                                                    @if($leaveRequest->status === 'pending')
                                                        <span class="text-gray-500 text-xs">Waiting for approval</span>
                                                    @endif
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if($leaveRequests->hasPages())
                        <div class="mt-4">
                            {{ $leaveRequests->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 