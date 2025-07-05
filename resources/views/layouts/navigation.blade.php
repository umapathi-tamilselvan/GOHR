<nav class="flex-1 px-2 py-4 space-y-2">
    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
        {{ __('Dashboard') }}
    </x-nav-link>
    <x-nav-link :href="route('leave-requests.index')" :active="request()->routeIs('leave-requests.index')">
        {{ __('Leave Requests') }}
    </x-nav-link>
    @can('viewAny', App\Models\Attendance::class)
        <x-nav-link :href="route('attendance.index')" :active="request()->routeIs('attendance.index')">
            {{ __('Attendance') }}
        </x-nav-link>
    @endcan
    @can('viewAny', App\Models\User::class)
    <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.index')">
        {{ __('User Management') }}
    </x-nav-link>
    @endcan
    @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('HR'))
        <x-nav-link :href="route('projects.index')" :active="request()->routeIs('projects.index')">
            {{ __('Projects') }}
        </x-nav-link>
    @endif
    @if(auth()->user()->hasRole('Super Admin'))
        <x-nav-link :href="route('organizations.index')" :active="request()->routeIs('organizations.index')">
            {{ __('Organizations') }}
        </x-nav-link>
        <x-nav-link :href="route('roles.index')" :active="request()->routeIs('roles.index')">
            {{ __('Roles') }}
        </x-nav-link>
    @endif
    @role('Super Admin')
    <x-nav-link :href="route('audit-log.index')" :active="request()->routeIs('audit-log.index')">
        {{ __('Audit Log') }}
    </x-nav-link>
    @endrole
</nav>
