<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\ServiceProvider as BaseServiceProvider;

class EventServiceProvider extends BaseServiceProvider
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
        // Add your custom events here
        // 'App\Events\SomeEvent' => [
        //     'App\Listeners\EventListener',
        // ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        try {
            parent::boot();
        } catch (\Exception $e) {
            // Ignore database errors during bootstrap
        }
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
