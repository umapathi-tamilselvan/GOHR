<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col h-full">
    <!-- Navigation Items -->
    <div class="flex-1 px-4 py-6 space-y-2">
        <!-- Dashboard -->
        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="group">
            <div class="flex items-center space-x-3 px-3 py-2 rounded-lg transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('dashboard') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                </svg>
                <span class="font-medium">{{ __('Dashboard') }}</span>
            </div>
        </x-nav-link>

        <!-- Employee Attendance -->
        @if(auth()->user()->hasRole('Employee'))
            <x-nav-link :href="route('attendances.index')" :active="request()->routeIs('attendances.index')" class="group">
                <div class="flex items-center space-x-3 px-3 py-2 rounded-lg transition-all duration-200 {{ request()->routeIs('attendances.index') ? 'bg-green-50 text-green-700 border-r-2 border-green-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('attendances.index') ? 'text-green-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="font-medium">{{ __('My Attendance') }}</span>
                </div>
            </x-nav-link>
        @endif

        <!-- Leave Management -->
        @can('viewAny', App\Models\Leave::class)
            <div class="pt-4">
                <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Leave Management') }}</h3>
                <div class="mt-2 space-y-1">
                    <x-nav-link :href="route('leaves.index')" :active="request()->routeIs('leaves.index')" class="group">
                        <div class="flex items-center space-x-3 px-3 py-2 rounded-lg transition-all duration-200 {{ request()->routeIs('leaves.index') ? 'bg-emerald-50 text-emerald-700 border-r-2 border-emerald-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('leaves.index') ? 'text-emerald-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="font-medium">{{ __('Leave Requests') }}</span>
                        </div>
                    </x-nav-link>

                    <x-nav-link :href="route('leaves.calendar')" :active="request()->routeIs('leaves.calendar')" class="group">
                        <div class="flex items-center space-x-3 px-3 py-2 rounded-lg transition-all duration-200 {{ request()->routeIs('leaves.calendar') ? 'bg-cyan-50 text-cyan-700 border-r-2 border-cyan-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('leaves.calendar') ? 'text-cyan-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="font-medium">{{ __('Calendar View') }}</span>
                        </div>
                    </x-nav-link>

                    <x-nav-link :href="route('leave-balances.index')" :active="request()->routeIs('leave-balances.*')" class="group">
                        <div class="flex items-center space-x-3 px-3 py-2 rounded-lg transition-all duration-200 {{ request()->routeIs('leave-balances.*') ? 'bg-amber-50 text-amber-700 border-r-2 border-amber-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('leave-balances.*') ? 'text-amber-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                            </svg>
                            <span class="font-medium">{{ __('Leave Balances') }}</span>
                        </div>
                    </x-nav-link>

                    <x-nav-link :href="route('leaves.report')" :active="request()->routeIs('leaves.report')" class="group">
                        <div class="flex items-center space-x-3 px-3 py-2 rounded-lg transition-all duration-200 {{ request()->routeIs('leaves.report') ? 'bg-violet-50 text-violet-700 border-r-2 border-violet-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('leaves.report') ? 'text-violet-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <span class="font-medium">{{ __('Leave Reports') }}</span>
                        </div>
                    </x-nav-link>
                </div>
            </div>
        @endcan

        <!-- Attendance Management (HR/Manager) -->
        @can('viewAny', App\Models\Attendance::class)
            <div class="pt-4">
                <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Attendance') }}</h3>
                <div class="mt-2 space-y-1">
                    <x-nav-link :href="route('attendances.list')" :active="request()->routeIs('attendances.list')" class="group">
                        <div class="flex items-center space-x-3 px-3 py-2 rounded-lg transition-all duration-200 {{ request()->routeIs('attendances.list') ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('attendances.list') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <span class="font-medium">{{ __('Attendance Log') }}</span>
                        </div>
                    </x-nav-link>
                
                    <x-nav-link :href="route('attendances.report')" :active="request()->routeIs('attendances.report')" class="group">
                        <div class="flex items-center space-x-3 px-3 py-2 rounded-lg transition-all duration-200 {{ request()->routeIs('attendances.report') ? 'bg-purple-50 text-purple-700 border-r-2 border-purple-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('attendances.report') ? 'text-purple-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <span class="font-medium">{{ __('Reports') }}</span>
                        </div>
                    </x-nav-link>

                    <x-nav-link :href="route('attendances.manage')" :active="request()->routeIs('attendances.manage')" class="group">
                        <div class="flex items-center space-x-3 px-3 py-2 rounded-lg transition-all duration-200 {{ request()->routeIs('attendances.manage') ? 'bg-orange-50 text-orange-700 border-r-2 border-orange-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('attendances.manage') ? 'text-orange-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                            </svg>
                            <span class="font-medium">{{ __('Manage') }}</span>
                        </div>
                    </x-nav-link>
                </div>
            </div>
        @endcan

        <!-- User Management -->
        @can('viewAny', App\Models\User::class)
            <div class="pt-4">
                <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Management') }}</h3>
                <div class="mt-2 space-y-1">
                    <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.index')" class="group">
                        <div class="flex items-center space-x-3 px-3 py-2 rounded-lg transition-all duration-200 {{ request()->routeIs('users.index') ? 'bg-indigo-50 text-indigo-700 border-r-2 border-indigo-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('users.index') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.653-.184-1.268-.5-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.653.184-1.268.5-1.857m0 0a5.002 5.002 0 019 0m-4.5 4.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5zM12 12a5 5 0 100-10 5 5 0 000 10z"></path>
                            </svg>
                            <span class="font-medium">{{ __('Users') }}</span>
                        </div>
                    </x-nav-link>

                    <x-nav-link :href="route('employees.index')" :active="request()->routeIs('employees.*')" class="group">
                        <div class="flex items-center space-x-3 px-3 py-2 rounded-lg transition-all duration-200 {{ request()->routeIs('employees.*') ? 'bg-pink-50 text-pink-700 border-r-2 border-pink-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('employees.*') ? 'text-pink-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.653-.184-1.268-.5-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.653.184-1.268.5-1.857m0 0a5.002 5.002 0 019 0m-4.5 4.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5zM12 12a5 5 0 100-10 5 5 0 000 10z"></path>
                            </svg>
                            <span class="font-medium">{{ __('Employees') }}</span>
                        </div>
                    </x-nav-link>

                    <x-nav-link :href="route('projects.index')" :active="request()->routeIs('projects.*')" class="group">
                        <div class="flex items-center space-x-3 px-3 py-2 rounded-lg transition-all duration-200 {{ request()->routeIs('projects.*') ? 'bg-teal-50 text-teal-700 border-r-2 border-teal-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('projects.*') ? 'text-teal-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <span class="font-medium">{{ __('Projects') }}</span>
                        </div>
                    </x-nav-link>
                </div>
            </div>
        @endcan

        <!-- System -->
        @can('view_audit_log')
            <div class="pt-4">
                <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('System') }}</h3>
                <div class="mt-2 space-y-1">
                    <x-nav-link :href="route('audit-log.index')" :active="request()->routeIs('audit-log.index')" class="group">
                        <div class="flex items-center space-x-3 px-3 py-2 rounded-lg transition-all duration-200 {{ request()->routeIs('audit-log.index') ? 'bg-red-50 text-red-700 border-r-2 border-red-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('audit-log.index') ? 'text-red-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                            <span class="font-medium">{{ __('Audit Log') }}</span>
                        </div>
                    </x-nav-link>
                </div>
            </div>
        @endcan
    </div>

    <!-- User Profile Section -->
    <div class="border-t border-gray-200 p-4">
        <div class="flex items-center space-x-3">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                    <span class="text-sm font-medium text-blue-600">{{ substr(auth()->user()->name, 0, 1) }}</span>
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
            </div>
        </div>
    </div>
</div>
