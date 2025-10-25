<?php

namespace App\Providers;

use App\Payment\Events\PaymentSettled;
use App\Payment\Listeners\QueuePaymentConfirmationEmail;
use App\Registration\Events\ApplicantRegisteredEvent;
use App\Registration\Listeners\SendApplicantRegisteredEmail;
use App\View\Composers\SiteSettingComposer;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        // Share site settings with specific components
        View::composer([
            'components.hero',
            'components.registration-flow',
            'components.registration-waves',
            'components.requirements',
            'components.faq',
        ], SiteSettingComposer::class);

        Event::listen(ApplicantRegisteredEvent::class, SendApplicantRegisteredEmail::class);
        Event::listen(PaymentSettled::class, QueuePaymentConfirmationEmail::class);
    }
}
