<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col py-4">
            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-nav-link>

            @if(auth()->user()->hasRole('Employee'))
                <x-nav-link :href="route('attendances.index')" :active="request()->routeIs('attendances.index')">
                    {{ __('Attendance') }}
                </x-nav-link>
            @endif

            @can('viewAny', App\Models\Attendance::class)
                <x-nav-link :href="route('attendances.list')" :active="request()->routeIs('attendances.list')">
                    {{ __('Attendance Log') }}
                </x-nav-link>
            
                <x-nav-link :href="route('attendances.report')" :active="request()->routeIs('attendances.report')">
                    {{ __('Attendance Report') }}
                </x-nav-link>

                <x-nav-link :href="route('attendances.manage')" :active="request()->routeIs('attendances.manage')">
                    {{ __('Manage Attendances') }}
                </x-nav-link>
            @endcan

            @can('viewAny', App\Models\User::class)
                <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.index')">
                    {{ __('User Management') }}
                </x-nav-link>
            @endcan

            @can('view_audit_log')
                <x-nav-link :href="route('audit-log.index')" :active="request()->routeIs('audit-log.index')">
                    {{ __('Audit Log') }}
                </x-nav-link>
            @endcan
        </div>
    </div>
</nav>
