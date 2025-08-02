<?php

namespace App\Providers;

use App\Models\Leave;
use App\Observers\LeaveObserver;
use Illuminate\Auth\Events\Login;
use App\Listeners\LoginSuccessful;
use Illuminate\Auth\Events\Logout;
use App\Listeners\LogoutSuccessful;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        Login::class => [
            LoginSuccessful::class,
        ],
        Logout::class => [
            LogoutSuccessful::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        Leave::observe(LeaveObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
} 