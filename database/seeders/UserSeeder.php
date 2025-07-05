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
        $this->createHrUser();
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

    private function createHrUser()
    {
        $organization = Organization::create([
            'name' => 'Test Organization',
            'slug' => Str::slug('Test Organization'),
        ]);

        $user = User::create([
            'name' => 'HR User',
            'email' => 'hr@example.com',
            'password' => bcrypt('password'),
            'organization_id' => $organization->id,
        ]);

        $user->assignRole('HR');
    }
}
