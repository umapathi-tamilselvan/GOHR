<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProjectTaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        $this->authorize('view', $project);

        $tasks = $project->tasks()
                        ->with('assignedUser')
                        ->latest()
                        ->paginate(15);

        return view('projects.tasks.index', compact('project', 'tasks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Project $project)
    {
        $this->authorize('update', $project);

        $user = Auth::user();
        
        // Get available users for assignment based on user role
        if ($user->hasRole('Super Admin')) {
            $users = User::all();
        } else {
            $users = User::where('organization_id', $user->organization_id)->get();
        }

        return view('projects.tasks.create', compact('project', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => ['required', Rule::in(['pending', 'in_progress', 'completed', 'cancelled'])],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'due_date' => 'nullable|date|after_or_equal:today',
        ]);

        $validated['project_id'] = $project->id;

        // Set completed_date if status is completed
        if ($validated['status'] === 'completed') {
            $validated['completed_date'] = now()->toDateString();
        }

        ProjectTask::create($validated);

        return redirect()->route('projects.tasks.index', $project)
                        ->with('success', 'Task created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, ProjectTask $task)
    {
        $this->authorize('view', $project);

        $task->load('assignedUser');

        return view('projects.tasks.show', compact('project', 'task'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project, ProjectTask $task)
    {
        $this->authorize('update', $project);

        $user = Auth::user();
        
        // Get available users for assignment based on user role
        if ($user->hasRole('Super Admin')) {
            $users = User::all();
        } else {
            $users = User::where('organization_id', $user->organization_id)->get();
        }

        return view('projects.tasks.edit', compact('project', 'task', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, ProjectTask $task)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => ['required', Rule::in(['pending', 'in_progress', 'completed', 'cancelled'])],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'due_date' => 'nullable|date',
        ]);

        // Set completed_date if status is completed and wasn't completed before
        if ($validated['status'] === 'completed' && $task->status !== 'completed') {
            $validated['completed_date'] = now()->toDateString();
        } elseif ($validated['status'] !== 'completed') {
            $validated['completed_date'] = null;
        }

        $task->update($validated);

        return redirect()->route('projects.tasks.index', $project)
                        ->with('success', 'Task updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, ProjectTask $task)
    {
        $this->authorize('update', $project);

        $task->delete();

        return redirect()->route('projects.tasks.index', $project)
                        ->with('success', 'Task deleted successfully.');
    }

    /**
     * Update task status.
     */
    public function updateStatus(Request $request, Project $project, ProjectTask $task)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'in_progress', 'completed', 'cancelled'])],
        ]);

        $updateData = ['status' => $validated['status']];

        // Set completed_date if status is completed
        if ($validated['status'] === 'completed') {
            $updateData['completed_date'] = now()->toDateString();
        } elseif ($task->status === 'completed' && $validated['status'] !== 'completed') {
            $updateData['completed_date'] = null;
        }

        $task->update($updateData);

        return redirect()->route('projects.tasks.index', $project)
                        ->with('success', 'Task status updated successfully.');
    }

    /**
     * Assign task to user.
     */
    public function assign(Request $request, Project $project, ProjectTask $task)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $task->update(['assigned_to' => $validated['assigned_to']]);

        return redirect()->route('projects.tasks.index', $project)
                        ->with('success', 'Task assigned successfully.');
    }

    /**
     * Display task reports for a project.
     */
    public function report(Project $project)
    {
        $this->authorize('view', $project);

        $tasks = $project->tasks()->with('assignedUser')->get();

        $stats = [
            'total_tasks' => $tasks->count(),
            'pending_tasks' => $tasks->where('status', 'pending')->count(),
            'in_progress_tasks' => $tasks->where('status', 'in_progress')->count(),
            'completed_tasks' => $tasks->where('status', 'completed')->count(),
            'cancelled_tasks' => $tasks->where('status', 'cancelled')->count(),
            'overdue_tasks' => $tasks->filter(function ($task) {
                return $task->isOverdue();
            })->count(),
            'due_today_tasks' => $tasks->filter(function ($task) {
                return $task->isDueToday();
            })->count(),
        ];

        $tasksByPriority = [
            'urgent' => $tasks->where('priority', 'urgent')->count(),
            'high' => $tasks->where('priority', 'high')->count(),
            'medium' => $tasks->where('priority', 'medium')->count(),
            'low' => $tasks->where('priority', 'low')->count(),
        ];

        $tasksByAssignee = $tasks->groupBy('assigned_to')
                                ->map(function ($group) {
                                    return $group->count();
                                });

        return view('projects.tasks.report', compact('project', 'tasks', 'stats', 'tasksByPriority', 'tasksByAssignee'));
    }
}
