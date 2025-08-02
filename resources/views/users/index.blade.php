<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h4 mb-1 text-dark">
                    {{ __('User Management') }}
                </h2>
                <p class="text-muted mb-0">Manage system users and their permissions</p>
            </div>
            @can('create', App\Models\User::class)
                <button
                    x-data=""
                    x-on:click.prevent="$dispatch('open-modal', 'add-user-modal')"
                    class="btn btn-primary btn-lg shadow-sm"
                >
                    <i class="fas fa-plus me-2"></i>
                    {{ __('Add User') }}
                </button>
            @endcan
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Search and Filters Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-search me-2"></i>Search & Filters
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.index') }}" method="GET">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Search Users</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" name="search" id="search" placeholder="Search by name or email..." 
                                           class="form-control" value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="role" class="form-label">Filter by Role</label>
                                <select name="role" id="role" class="form-select">
                                    <option value="">All Roles</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                                            {{ ucfirst($role->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i>Search
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Users Table Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Users ({{ $users->total() }})</h5>
                        <small class="text-muted">
                            Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} results
                        </small>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    @if($users->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" class="border-0">
                                            <i class="fas fa-user me-2"></i>User
                                        </th>
                                        <th scope="col" class="border-0">Email</th>
                                        <th scope="col" class="border-0">Role</th>
                                        @if(auth()->user()->hasRole('Super Admin'))
                                            <th scope="col" class="border-0">Organization</th>
                                        @endif
                                        <th scope="col" class="border-0">Status</th>
                                        @if(auth()->user()->hasAnyRole(['super-admin', 'hr', 'manager']))
                                            <th scope="col" class="border-0">Actions</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="avatar avatar-md bg-primary rounded-circle d-flex align-items-center justify-content-center">
                                                            <span class="text-white fw-bold">{{ substr($user->name, 0, 1) }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        @can('view', $user)
                                                            <a href="{{ route('users.show', $user) }}" class="text-decoration-none fw-semibold text-primary">
                                                                {{ $user->name }}
                                                            </a>
                                                        @else
                                                            <div class="fw-semibold text-dark">{{ $user->name }}</div>
                                                        @endcan
                                                        <div class="text-muted small">Member since {{ $user->created_at->format('M Y') }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-dark">{{ $user->email }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($user->roles as $role)
                                                        <span class="badge bg-primary">{{ $role->name }}</span>
                                                    @endforeach
                                                </div>
                                            </td>
                                            @if(auth()->user()->hasRole('Super Admin'))
                                                <td>
                                                    <span class="text-dark">{{ $user->organization->name ?? 'N/A' }}</span>
                                                </td>
                                            @endif
                                            <td>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-circle me-1"></i>Active
                                                </span>
                                            </td>
                                            @if(auth()->user()->hasAnyRole(['super-admin', 'hr', 'manager']))
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        @can('view', $user)
                                                            <a href="{{ route('users.show', $user) }}" 
                                                               class="btn btn-outline-info" title="View User">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        @endcan
                                                        @can('update', $user)
                                                            <a href="{{ route('users.edit', $user) }}" 
                                                               class="btn btn-outline-warning" title="Edit User">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        @endcan
                                                        @if(auth()->user()->hasRole('super-admin') || 
                                                            (auth()->user()->hasRole('hr') && $user->organization_id === auth()->user()->organization_id) ||
                                                            (auth()->user()->hasRole('manager') && $user->manager_id === auth()->user()->id))
                                                            <a href="{{ route('leaves.create', ['user_id' => $user->id]) }}" 
                                                               class="btn btn-outline-success" title="Apply Leave">
                                                                <i class="fas fa-calendar-plus"></i>
                                                            </a>
                                                        @endif
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fas fa-users fa-3x text-muted"></i>
                            </div>
                            <h5 class="text-muted">No users found</h5>
                            <p class="text-muted">No users match your search criteria.</p>
                            @can('create', App\Models\User::class)
                                <div class="mt-3">
                                    <button
                                        x-data=""
                                        x-on:click.prevent="$dispatch('open-modal', 'add-user-modal')"
                                        class="btn btn-primary"
                                    >
                                        <i class="fas fa-plus me-2"></i>Add User
                                    </button>
                                </div>
                            @endcan
                        </div>
                    @endif
                </div>

                @if($users->hasPages())
                    <div class="card-footer bg-white border-top">
                        <div class="d-flex justify-content-center">
                            {{ $users->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @include('users.partials.add-user-modal', ['organizations' => $organizations, 'roles' => $roles])

    <style>
        .avatar {
            width: 40px;
            height: 40px;
        }
        .avatar-md {
            width: 48px;
            height: 48px;
        }
        .table > :not(caption) > * > * {
            padding: 1rem 0.75rem;
        }
        .btn-group-sm > .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
</x-app-layout> 