<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::with('manager', 'users')->latest()->get();
        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $managers = User::whereHas('roles', fn($q) => $q->where('name', 'Manager'))->get();
        $users = User::whereHas('roles', fn($q) => $q->where('name', 'Employee'))->get();
        return view('projects.create', compact('managers', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'users' => 'array'
        ]);

        $project = Project::create($request->only('name', 'description', 'manager_id'));
        $project->users()->sync($request->users);

        return redirect()->route('projects.index')->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        $managers = User::whereHas('roles', fn($q) => $q->where('name', 'Manager'))->get();
        $users = User::whereHas('roles', fn($q) => $q->where('name', 'Employee'))->get();
        return view('projects.edit', compact('project', 'managers', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'users' => 'array'
        ]);

        $project->update($request->only('name', 'description', 'manager_id'));
        $project->users()->sync($request->users);

        return redirect()->route('projects.index')->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
    }
}
