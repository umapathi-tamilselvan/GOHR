<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Designation;
use Illuminate\Support\Facades\Hash;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($organization) {
            // Create default roles
            foreach (['HR', 'Manager', 'Employee'] as $roleName) {
                Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            }

            // Create default HR user
            $hrUser = User::create([
                'name' => 'HR of ' . $organization->name,
                'email' => 'hr@' . strtolower($organization->slug) . '.com',
                'password' => Hash::make('password'),
                'organization_id' => $organization->id,
            ]);
            $hrUser->assignRole('HR');
            
            // Create default designations from the Designation model
            $designations = Designation::getDefaults();

            foreach ($designations as $designation) {
                Designation::firstOrCreate($designation);
            }
        });
    }
}
