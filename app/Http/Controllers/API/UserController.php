<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        try {
            $query = User::with(['roles', 'organization']);

            // Search by name or email
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Filter by role
            if ($request->has('role') && $request->role) {
                $query->whereHas('roles', function($q) use ($request) {
                    $q->where('name', $request->role);
                });
            }

            // Filter by organization
            if ($request->has('organization_id') && $request->organization_id) {
                $query->where('organization_id', $request->organization_id);
            }

            $users = $query->paginate($request->get('per_page', 15));

            // Add role information to each user
            $users->getCollection()->transform(function ($user) {
                $role = $user->roles->first();
                $user->role = $role ? $role->name : 'employee';
                return $user;
            });

            return response()->json([
                'success' => true,
                'message' => 'Users retrieved successfully',
                'data' => $users->items(),
                'meta' => [
                    'current_page' => $users->currentPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'last_page' => $users->lastPage()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'role' => 'required|string|in:employee,manager,hr,super-admin',
                'organization_id' => 'required|exists:organizations,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'organization_id' => $request->organization_id,
            ]);

            // Assign role
            $user->assignRole($request->role);

            // Load relationships
            $user->load(['roles', 'organization']);
            $role = $user->roles->first();
            $user->role = $role ? $role->name : 'employee';

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        try {
            $user->load(['roles', 'organization']);
            $role = $user->roles->first();
            $user->role = $role ? $role->name : 'employee';

            return response()->json([
                'success' => true,
                'message' => 'User retrieved successfully',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'role' => 'required|string|in:employee,manager,hr,super-admin',
                'organization_id' => 'required|exists:organizations,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'organization_id' => $request->organization_id,
            ]);

            // Update role
            $user->syncRoles([$request->role]);

            // Load relationships
            $user->load(['roles', 'organization']);
            $role = $user->roles->first();
            $user->role = $role ? $role->name : 'employee';

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        try {
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search users
     */
    public function search(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'query' => 'required|string|min:2'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = $request->get('query');
            $users = User::where('name', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%")
                        ->with(['roles', 'organization'])
                        ->limit(10)
                        ->get();

            // Add role information to each user
            $users->transform(function ($user) {
                $role = $user->roles->first();
                $user->role = $role ? $role->name : 'employee';
                return $user;
            });

            return response()->json([
                'success' => true,
                'message' => 'Search completed successfully',
                'data' => $users
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all organizations
     */
    public function organizations()
    {
        try {
            $organizations = \App\Models\Organization::all();

            return response()->json([
                'success' => true,
                'message' => 'Organizations retrieved successfully',
                'data' => $organizations
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve organizations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all departments (placeholder - departments not implemented yet)
     */
    public function departments()
    {
        try {
            // For now, return empty array since departments are not implemented
            // This can be updated when departments are added to the system
            $departments = [];

            return response()->json([
                'success' => true,
                'message' => 'Departments retrieved successfully',
                'data' => $departments
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve departments: ' . $e->getMessage()
            ], 500);
        }
    }
} 