<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use App\Listeners\CheckUserExpirationOnLogin;
use App\Models\User;
use App\Models\LeaveRequest;
use App\Models\Order;
use App\Observers\UserObserver;
use App\Observers\LeaveRequestObserver;
use App\Observers\OrderObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register User Observer for auto-generating leave balances
        User::observe(UserObserver::class);
        
        // Register LeaveRequest Observer for auto-filling user_id
        LeaveRequest::observe(LeaveRequestObserver::class);
        
        // Register Order Observer for tracking last edited by
        Order::observe(OrderObserver::class);
        
        // Register login event listener for daily expiration welcome notifications
        Event::listen(
            Login::class,
            CheckUserExpirationOnLogin::class
        );
    }
}
