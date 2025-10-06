<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\View\Composers\SiteSettingComposer;

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
            'components.requirements',
            'components.faq',
        ], SiteSettingComposer::class);
    }
}
