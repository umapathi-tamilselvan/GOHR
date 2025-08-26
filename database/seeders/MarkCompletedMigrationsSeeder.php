<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MarkCompletedMigrationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mark the migrations that already have tables as completed
        $completedMigrations = [
            '2025_01_27_000004_create_employees_table',
            '2025_01_27_000005_create_employee_profiles_table',
            '2025_01_27_000007_create_employee_documents_table',
        ];

        foreach ($completedMigrations as $migration) {
            // Check if migration is not already in migrations table
            $exists = DB::table('migrations')->where('migration', $migration)->exists();
            
            if (!$exists) {
                DB::table('migrations')->insert([
                    'migration' => $migration,
                    'batch' => 8, // Use next batch number
                ]);
                $this->command->info("Marked migration as completed: {$migration}");
            }
        }
    }
}
