<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createSuperAdmin();
        $this->createTestOrganizationUsers();
    }

    private function createSuperAdmin()
    {
        $user = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password'),
        ]);

        $user->assignRole('Super Admin');
    }

    private function createTestOrganizationUsers()
    {
        $organization = Organization::create([
            'name' => 'Test Organization',
            'slug' => Str::slug('Test Organization'),
        ]);

        // HR User
        $hrUser = User::create([
            'name' => 'HR User',
            'email' => 'hr@example.com',
            'password' => bcrypt('password'),
            'organization_id' => $organization->id,
        ]);
        $hrUser->assignRole('HR');

        // Manager User
        $managerUser = User::create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
            'organization_id' => $organization->id,
        ]);
        $managerUser->assignRole('Manager');

        // Employee Users
        for ($i = 1; $i <= 3; $i++) {
            $employee = User::create([
                'name' => 'Employee ' . $i,
                'email' => 'employee' . $i . '@example.com',
                'password' => bcrypt('password'),
                'organization_id' => $organization->id,
            ]);
            $employee->assignRole('Employee');
        }
    }
}
