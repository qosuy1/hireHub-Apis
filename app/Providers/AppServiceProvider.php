<?php

namespace App\Providers;

use App\Infrastructure\Notification\EmailNotifier;
use App\Infrastructure\Notification\Mailer;
use App\Interfaces\NotifierInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // this line mean that any class call the NotifierInterface gave him the EmailNotifier
        // and the EmailNotifier injected Mailer to send the email
        $this->app->bind(NotifierInterface::class, function(){
            return new EmailNotifier(new Mailer());
        }); 
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
       
    }
}
