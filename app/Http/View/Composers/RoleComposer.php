<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class RoleComposer
{
    public function compose(View $view)
    {
        $role = null;
        $roleColor = 'gray';

        if (Auth::check()) {
            $user = Auth::user();
            $role = $user->primary_role;

            $roleColor = match ($role) {
                'Super Admin' => 'blue',
                'HR' => 'green',
                'Manager' => 'orange',
                default => 'gray',
            };
        }

        $view->with('role', $role)->with('roleColor', $roleColor);
    }
} 