<?php

namespace App\Providers;

use App\Payment\Events\PaymentSettled;
use App\Payment\Listeners\QueuePaymentConfirmationEmail;
use App\Registration\Events\ApplicantRegisteredEvent;
use App\Registration\Listeners\SendApplicantRegisteredEmail;
use App\Services\Email\EmailServiceInterface;
use App\Services\Email\GmailEmailService;
use App\Services\Email\LaravelEmailService;
use App\Services\GmailMailableSender;
use App\Settings\CacheSettingsRepository;
use App\Settings\SettingsRepositoryInterface;
use App\View\Composers\SiteSettingComposer;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SettingsRepositoryInterface::class, CacheSettingsRepository::class);

        // Register email services
        $this->app->singleton(GmailEmailService::class, function ($app) {
            return new GmailEmailService($app->make(GmailMailableSender::class));
        });

        $this->app->singleton(LaravelEmailService::class);

        // Auto-resolve email service - Default ke Gmail API
        $this->app->bind(EmailServiceInterface::class, function ($app) {
            // Default selalu Gmail API, LaravelEmailService hanya untuk emergency fallback
            $gmailService = $app->make(GmailEmailService::class);

            // Jika Gmail API healthy, gunakan Gmail
            if ($gmailService->isHealthy()) {
                return $gmailService;
            }

            // Fallback ke Laravel Mail jika Gmail API bermasalah
            Log::warning('Gmail API not healthy, falling back to Laravel Mail');
            return $app->make(LaravelEmailService::class);
        });
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
