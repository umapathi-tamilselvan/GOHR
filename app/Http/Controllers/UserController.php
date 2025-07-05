<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use App\Models\Designation;
use App\Models\Project;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);

        $user = Auth::user();
        if ($user->hasRole('Super Admin')) {
            $users = User::with('organization')->latest()->paginate(10);
        } else {
            $users = User::where('organization_id', $user->organization_id)->with('organization')->latest()->paginate(10);
        }

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', User::class);

        $user = Auth::user();
        $organizations = $user->hasRole('Super Admin') ? Organization::all() : Organization::where('id', $user->organization_id)->get();
        $roles = Role::pluck('name', 'id');
        $designations = Designation::all();
        $projects = Project::all();

        return view('users.create', compact('user', 'organizations', 'roles', 'designations', 'projects'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'designation_id' => ['nullable', 'exists:designations,id'],
            'roles' => ['required', 'array'],
            'projects' => ['nullable', 'array'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'organization_id' => $request->organization_id,
            'designation_id' => $request->designation_id,
        ]);

        $roles = Role::whereIn('id', $request->roles)->get();
        $user->syncRoles($roles);
        $user->projects()->sync($request->projects);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $organizations = Auth::user()->hasRole('Super Admin') ? Organization::all() : Organization::where('id', Auth::user()->organization_id)->get();
        $roles = Role::pluck('name', 'id');
        $userRoles = $user->roles->pluck('id')->toArray();
        $designations = Designation::all();
        $projects = Project::all();
        $userProjects = $user->projects->pluck('id')->toArray();

        return view('users.edit', compact('user', 'organizations', 'roles', 'userRoles', 'designations', 'projects', 'userProjects'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8'],
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'designation_id' => ['nullable', 'exists:designations,id'],
            'roles' => ['required', 'array'],
            'projects' => ['nullable', 'array'],
        ]);

        $userData = $request->only('name', 'email', 'organization_id', 'designation_id');
        if ($request->filled('password')) {
            $userData['password'] = bcrypt($request->password);
        }

        $user->update($userData);

        $roles = Role::whereIn('id', $request->roles)->get();
        $user->syncRoles($roles);
        $user->projects()->sync($request->projects);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
