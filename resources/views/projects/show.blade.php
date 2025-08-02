<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $project->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('projects.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Back to Projects') }}
                </a>
                @can('update', $project)
                    <a href="{{ route('projects.edit', $project) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        {{ __('Edit Project') }}
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Project Details -->
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Project Details</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Description</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ $project->description ?: 'No description provided.' }}</p>
                                </div>
                                
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Status</h4>
                                    @php
                                        $statusClasses = [
                                            'active' => 'bg-green-100 text-green-800',
                                            'completed' => 'bg-blue-100 text-blue-800',
                                            'on_hold' => 'bg-yellow-100 text-yellow-800',
                                            'cancelled' => 'bg-red-100 text-red-800'
                                        ];
                                        $statusClass = $statusClasses[$project->status] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="mt-1 inline-flex px-2 text-xs font-semibold rounded-full {{ $statusClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                    </span>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Start Date</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ $project->start_date->format('M d, Y') }}</p>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">End Date</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ $project->end_date ? $project->end_date->format('M d, Y') : 'Not set' }}</p>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Budget</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ $project->budget ? '$' . number_format($project->budget, 2) : 'Not set' }}</p>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Progress</h4>
                                    <div class="mt-1 flex items-center">
                                        <div class="w-24 bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $project->progress_percentage }}%"></div>
                                        </div>
                                        <span class="text-sm text-gray-500">{{ $project->progress_percentage }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Project Manager -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Project Manager</h3>
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-lg font-medium text-blue-600">{{ substr($project->manager->name, 0, 1) }}</span>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-sm font-medium text-gray-900">{{ $project->manager->name }}</h4>
                                    <p class="text-sm text-gray-500">{{ $project->manager->email }}</p>
                                    <p class="text-sm text-gray-500">{{ $project->manager->roles->pluck('name')->join(', ') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Project Tasks -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Tasks</h3>
                                @can('update', $project)
                                    <a href="{{ route('projects.tasks.create', $project) }}" class="inline-flex items-center px-3 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        {{ __('Add Task') }}
                                    </a>
                                @endcan
                            </div>

                            <!-- Task Statistics -->
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-gray-900">{{ $taskStats['total'] }}</div>
                                    <div class="text-sm text-gray-500">Total</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-yellow-600">{{ $taskStats['pending'] }}</div>
                                    <div class="text-sm text-gray-500">Pending</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-600">{{ $taskStats['in_progress'] }}</div>
                                    <div class="text-sm text-gray-500">In Progress</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-600">{{ $taskStats['completed'] }}</div>
                                    <div class="text-sm text-gray-500">Completed</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-red-600">{{ $taskStats['cancelled'] }}</div>
                                    <div class="text-sm text-gray-500">Cancelled</div>
                                </div>
                            </div>

                            <!-- Tasks List -->
                            <div class="space-y-3">
                                @forelse($tasks as $task)
                                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <h4 class="text-sm font-medium text-gray-900">{{ $task->title }}</h4>
                                                @if($task->description)
                                                    <p class="text-sm text-gray-500 mt-1">{{ Str::limit($task->description, 100) }}</p>
                                                @endif
                                                <div class="flex items-center mt-2 space-x-4">
                                                    @if($task->assignedUser)
                                                        <span class="text-xs text-gray-500">Assigned to: {{ $task->assignedUser->name }}</span>
                                                    @endif
                                                    @if($task->due_date)
                                                        <span class="text-xs text-gray-500">Due: {{ $task->due_date->format('M d, Y') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                @php
                                                    $priorityClasses = [
                                                        'urgent' => 'bg-red-100 text-red-800',
                                                        'high' => 'bg-orange-100 text-orange-800',
                                                        'medium' => 'bg-blue-100 text-blue-800',
                                                        'low' => 'bg-gray-100 text-gray-800'
                                                    ];
                                                    $priorityClass = $priorityClasses[$task->priority] ?? 'bg-gray-100 text-gray-800';
                                                    
                                                    $statusClasses = [
                                                        'completed' => 'bg-green-100 text-green-800',
                                                        'in_progress' => 'bg-blue-100 text-blue-800',
                                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                                        'cancelled' => 'bg-red-100 text-red-800'
                                                    ];
                                                    $statusClass = $statusClasses[$task->status] ?? 'bg-gray-100 text-gray-800';
                                                @endphp
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $priorityClass }}">
                                                    {{ ucfirst($task->priority) }}
                                                </span>
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusClass }}">
                                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-center text-gray-500 py-4">No tasks found for this project.</p>
                                @endforelse
                            </div>

                            @if($tasks->count() > 0)
                                <div class="mt-4 text-center">
                                    <a href="{{ route('projects.tasks.index', $project) }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                        View all tasks →
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <!-- Project Members -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Team Members</h3>
                                @can('update', $project)
                                    <button type="button" class="text-blue-600 hover:text-blue-900 text-sm font-medium" onclick="document.getElementById('add-member-modal').classList.remove('hidden')">
                                        Add Member
                                    </button>
                                @endcan
                            </div>

                            <div class="space-y-3">
                                @forelse($members as $member)
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                <span class="text-sm font-medium text-blue-600">{{ substr($member->user->name, 0, 1) }}</span>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">{{ $member->user->name }}</p>
                                                <p class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $member->role)) }}</p>
                                            </div>
                                        </div>
                                        @can('update', $project)
                                            @if($member->role !== 'manager')
                                                <form action="{{ route('projects.remove-member', [$project, $member->user]) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 text-xs" onclick="return confirm('Are you sure you want to remove this member?')">
                                                        Remove
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan
                                    </div>
                                @empty
                                    <p class="text-center text-gray-500 py-4">No team members found.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                            <div class="space-y-2">
                                @can('update', $project)
                                    <a href="{{ route('projects.tasks.create', $project) }}" class="block w-full text-left px-3 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-md">
                                        Add New Task
                                    </a>
                                @endcan
                                <a href="{{ route('projects.tasks.index', $project) }}" class="block w-full text-left px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md">
                                    View All Tasks
                                </a>
                                <a href="{{ route('projects.tasks.report', $project) }}" class="block w-full text-left px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md">
                                    Task Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Member Modal -->
    @can('update', $project)
        <div id="add-member-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Add Team Member</h3>
                    <form action="{{ route('projects.add-member', $project) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="user_id" class="block text-sm font-medium text-gray-700">Select User</label>
                            <select id="user_id" name="user_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">Select User</option>
                                @foreach(App\Models\User::where('organization_id', $project->organization_id)->get() as $user)
                                    @if(!$project->members()->where('user_id', $user->id)->exists())
                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->roles->pluck('name')->join(', ') }})</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                            <select id="role" name="role" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="member">Member</option>
                                <option value="team_lead">Team Lead</option>
                                <option value="observer">Observer</option>
                            </select>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="document.getElementById('add-member-modal').classList.add('hidden')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Add Member
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
</x-app-layout> 