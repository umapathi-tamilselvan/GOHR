<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class RoleComposer
{
    public function compose(View $view)
    {
        $color = 'indigo'; // Default color
        if (Auth::check()) {
            $user = Auth::user();
            switch ($user->getPrimaryRoleAttribute()) {
                case 'Super Admin':
                    $color = 'red';
                    break;
                case 'HR':
                    $color = 'blue';
                    break;
                case 'Manager':
                    $color = 'green';
                    break;
                case 'Employee':
                    $color = 'yellow';
                    break;
            }
        }
        $view->with('roleColor', $color);
    }
} 