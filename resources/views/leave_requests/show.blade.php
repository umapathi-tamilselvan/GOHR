<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Leave Request Details') }}
            </h2>
            <div class="flex space-x-2">
                @can('update', $leaveRequest)
                    <a href="{{ route('leave-requests.edit', $leaveRequest) }}" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600">
                        {{ __('Edit') }}
                    </a>
                @endcan
                <a href="{{ route('leave-requests.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600">
                    {{ __('Back to List') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold mb-4">{{ __('Request Information') }}</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('Applicant') }}</dt>
                                    <dd class="text-sm text-gray-900">{{ $leaveRequest->user->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('Leave Type') }}</dt>
                                    <dd class="text-sm text-gray-900">{{ $leaveRequest->leave_type }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('Start Date') }}</dt>
                                    <dd class="text-sm text-gray-900">{{ $leaveRequest->start_date }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('End Date') }}</dt>
                                    <dd class="text-sm text-gray-900">{{ $leaveRequest->end_date }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('Status') }}</dt>
                                    <dd class="text-sm">
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
                                                Approved by {{ $leaveRequest->approver->name }} on {{ $leaveRequest->approved_at->format('M d, Y h:i A') }}
                                            </div>
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold mb-4">{{ __('Reason') }}</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-900">{{ $leaveRequest->reason ?: 'No reason provided' }}</p>
                            </div>
                        </div>
                    </div>

                    @can('approve', $leaveRequest)
                        @if($leaveRequest->status === 'pending')
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <h3 class="text-lg font-semibold mb-4">{{ __('Actions') }}</h3>
                                <div class="flex space-x-4">
                                    <form action="{{ route('leave-requests.approve', $leaveRequest) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600">
                                            {{ __('Approve') }}
                                        </button>
                                    </form>
                                    <form action="{{ route('leave-requests.approve', $leaveRequest) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600">
                                            {{ __('Reject') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    @endcan
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 