<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectTask;
use App\Models\User;
use App\Models\Organization;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get organizations and users
        $organizations = Organization::all();
        $users = User::all();

        if ($organizations->isEmpty() || $users->isEmpty()) {
            $this->command->info('No organizations or users found. Please run UserSeeder first.');
            return;
        }

        foreach ($organizations as $organization) {
            $orgUsers = $users->where('organization_id', $organization->id);
            $managers = $orgUsers->filter(function ($user) {
                return $user->hasAnyRole(['Manager', 'HR']);
            });

            if ($managers->isEmpty()) {
                continue;
            }

            // Create sample projects
            $projects = [
                [
                    'name' => 'Website Redesign',
                    'description' => 'Complete redesign of the company website with modern UI/UX and improved functionality.',
                    'start_date' => now()->subDays(30),
                    'end_date' => now()->addDays(60),
                    'status' => 'active',
                    'budget' => 25000.00,
                ],
                [
                    'name' => 'Mobile App Development',
                    'description' => 'Development of a new mobile application for customer engagement and service delivery.',
                    'start_date' => now()->subDays(15),
                    'end_date' => now()->addDays(90),
                    'status' => 'active',
                    'budget' => 50000.00,
                ],
                [
                    'name' => 'Database Migration',
                    'description' => 'Migration of legacy database systems to modern cloud-based infrastructure.',
                    'start_date' => now()->subDays(60),
                    'end_date' => now()->addDays(30),
                    'status' => 'on_hold',
                    'budget' => 15000.00,
                ],
                [
                    'name' => 'Security Audit',
                    'description' => 'Comprehensive security audit and implementation of security best practices.',
                    'start_date' => now()->subDays(45),
                    'end_date' => now()->subDays(5),
                    'status' => 'completed',
                    'budget' => 10000.00,
                ],
            ];

            foreach ($projects as $projectData) {
                $manager = $managers->random();
                
                $project = Project::create([
                    'name' => $projectData['name'],
                    'description' => $projectData['description'],
                    'start_date' => $projectData['start_date'],
                    'end_date' => $projectData['end_date'],
                    'status' => $projectData['status'],
                    'manager_id' => $manager->id,
                    'organization_id' => $organization->id,
                    'budget' => $projectData['budget'],
                ]);

                // Add manager as project member
                ProjectMember::create([
                    'project_id' => $project->id,
                    'user_id' => $manager->id,
                    'role' => 'manager',
                    'joined_date' => $project->start_date,
                ]);

                // Add other team members
                $teamMembers = $orgUsers->where('id', '!=', $manager->id)->random(rand(2, 4));
                foreach ($teamMembers as $member) {
                    ProjectMember::create([
                        'project_id' => $project->id,
                        'user_id' => $member->id,
                        'role' => ['member', 'team_lead'][rand(0, 1)],
                        'joined_date' => $project->start_date->addDays(rand(1, 7)),
                    ]);
                }

                // Create sample tasks
                $taskTemplates = [
                    [
                        'title' => 'Project Planning',
                        'description' => 'Create detailed project plan and timeline',
                        'priority' => 'high',
                        'status' => 'completed',
                    ],
                    [
                        'title' => 'Requirements Gathering',
                        'description' => 'Collect and document project requirements',
                        'priority' => 'high',
                        'status' => 'completed',
                    ],
                    [
                        'title' => 'Design Phase',
                        'description' => 'Create wireframes and design mockups',
                        'priority' => 'medium',
                        'status' => 'in_progress',
                    ],
                    [
                        'title' => 'Development',
                        'description' => 'Implement core functionality',
                        'priority' => 'high',
                        'status' => 'in_progress',
                    ],
                    [
                        'title' => 'Testing',
                        'description' => 'Perform comprehensive testing',
                        'priority' => 'medium',
                        'status' => 'pending',
                    ],
                    [
                        'title' => 'Documentation',
                        'description' => 'Create user and technical documentation',
                        'priority' => 'low',
                        'status' => 'pending',
                    ],
                    [
                        'title' => 'Deployment',
                        'description' => 'Deploy to production environment',
                        'priority' => 'urgent',
                        'status' => 'pending',
                    ],
                ];

                foreach ($taskTemplates as $taskData) {
                    $assignedUser = $project->members->random()->user;
                    $dueDate = $project->end_date->subDays(rand(1, 30));
                    
                    ProjectTask::create([
                        'project_id' => $project->id,
                        'title' => $taskData['title'],
                        'description' => $taskData['description'],
                        'assigned_to' => $assignedUser->id,
                        'status' => $taskData['status'],
                        'priority' => $taskData['priority'],
                        'due_date' => $dueDate,
                        'completed_date' => $taskData['status'] === 'completed' ? $dueDate->subDays(rand(1, 5)) : null,
                    ]);
                }
            }
        }

        $this->command->info('Sample projects created successfully!');
    }
}
