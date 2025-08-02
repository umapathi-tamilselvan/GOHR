<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Project::with(['manager', 'organization']);

        // Filter by organization based on user role
        if ($user->hasRole('Super Admin')) {
            // Super Admin can see all projects
        } else {
            $query->forOrganization($user->organization_id);
        }

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $projects = $query->latest()->paginate(10);

        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        
        // Get available managers based on user role
        if ($user->hasRole('Super Admin')) {
            $managers = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['Manager', 'HR']);
            })->get();
        } else {
            $managers = User::where('organization_id', $user->organization_id)
                           ->whereHas('roles', function ($query) {
                               $query->whereIn('name', ['Manager', 'HR']);
                           })->get();
        }

        return view('projects.create', compact('managers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => ['required', Rule::in(['active', 'completed', 'on_hold', 'cancelled'])],
            'manager_id' => 'required|exists:users,id',
            'budget' => 'nullable|numeric|min:0',
        ]);

        // Set organization_id based on user role
        if ($user->hasRole('Super Admin')) {
            $validated['organization_id'] = User::find($request->manager_id)->organization_id;
        } else {
            $validated['organization_id'] = $user->organization_id;
        }

        $project = Project::create($validated);

        // Add manager as project member
        ProjectMember::create([
            'project_id' => $project->id,
            'user_id' => $project->manager_id,
            'role' => 'manager',
            'joined_date' => now()->toDateString(),
        ]);

        return redirect()->route('projects.show', $project)
                        ->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $this->authorize('view', $project);

        $project->load(['manager', 'organization', 'members.user', 'tasks.assignedUser']);
        
        $members = $project->members()->with('user')->active()->get();
        $tasks = $project->tasks()->with('assignedUser')->latest()->get();
        
        $taskStats = [
            'total' => $tasks->count(),
            'pending' => $tasks->where('status', 'pending')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'completed' => $tasks->where('status', 'completed')->count(),
            'cancelled' => $tasks->where('status', 'cancelled')->count(),
        ];

        return view('projects.show', compact('project', 'members', 'tasks', 'taskStats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        $this->authorize('update', $project);

        $user = Auth::user();
        
        // Get available managers based on user role
        if ($user->hasRole('Super Admin')) {
            $managers = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['Manager', 'HR']);
            })->get();
        } else {
            $managers = User::where('organization_id', $user->organization_id)
                           ->whereHas('roles', function ($query) {
                               $query->whereIn('name', ['Manager', 'HR']);
                           })->get();
        }

        return view('projects.edit', compact('project', 'managers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => ['required', Rule::in(['active', 'completed', 'on_hold', 'cancelled'])],
            'manager_id' => 'required|exists:users,id',
            'budget' => 'nullable|numeric|min:0',
        ]);

        $project->update($validated);

        return redirect()->route('projects.show', $project)
                        ->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()->route('projects.index')
                        ->with('success', 'Project deleted successfully.');
    }

    /**
     * Assign a manager to the project.
     */
    public function assignManager(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'manager_id' => 'required|exists:users,id',
        ]);

        $project->update(['manager_id' => $validated['manager_id']]);

        // Update or create project member record
        ProjectMember::updateOrCreate(
            ['project_id' => $project->id, 'user_id' => $validated['manager_id']],
            ['role' => 'manager', 'joined_date' => now()->toDateString()]
        );

        return redirect()->route('projects.show', $project)
                        ->with('success', 'Project manager assigned successfully.');
    }

    /**
     * Add a member to the project.
     */
    public function addMember(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => ['required', Rule::in(['manager', 'team_lead', 'member', 'observer'])],
        ]);

        // Check if user is already a member
        $existingMember = $project->members()->where('user_id', $validated['user_id'])->first();
        
        if ($existingMember) {
            return back()->with('error', 'User is already a member of this project.');
        }

        ProjectMember::create([
            'project_id' => $project->id,
            'user_id' => $validated['user_id'],
            'role' => $validated['role'],
            'joined_date' => now()->toDateString(),
        ]);

        return redirect()->route('projects.show', $project)
                        ->with('success', 'Member added to project successfully.');
    }

    /**
     * Remove a member from the project.
     */
    public function removeMember(Project $project, User $user)
    {
        $this->authorize('update', $project);

        $member = $project->members()->where('user_id', $user->id)->first();
        
        if (!$member) {
            return back()->with('error', 'User is not a member of this project.');
        }

        // Don't allow removing the project manager
        if ($member->role === 'manager') {
            return back()->with('error', 'Cannot remove the project manager.');
        }

        $member->update(['left_date' => now()->toDateString()]);

        return redirect()->route('projects.show', $project)
                        ->with('success', 'Member removed from project successfully.');
    }

    /**
     * Display project reports.
     */
    public function report(Request $request)
    {
        $user = Auth::user();
        $query = Project::with(['manager', 'organization', 'tasks']);

        // Filter by organization based on user role
        if (!$user->hasRole('Super Admin')) {
            $query->forOrganization($user->organization_id);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('end_date', '<=', $request->end_date);
        }

        $projects = $query->get();

        $stats = [
            'total_projects' => $projects->count(),
            'active_projects' => $projects->where('status', 'active')->count(),
            'completed_projects' => $projects->where('status', 'completed')->count(),
            'on_hold_projects' => $projects->where('status', 'on_hold')->count(),
            'cancelled_projects' => $projects->where('status', 'cancelled')->count(),
            'total_tasks' => $projects->sum(function ($project) {
                return $project->tasks->count();
            }),
            'completed_tasks' => $projects->sum(function ($project) {
                return $project->tasks->where('status', 'completed')->count();
            }),
        ];

        return view('projects.report', compact('projects', 'stats'));
    }
}
