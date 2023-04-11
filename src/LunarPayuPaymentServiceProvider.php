<?php

namespace Morawcik\LunarPayu;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Lunar\Facades\Payments;
use Morawcik\LunarPayu\Components\PaymentForm;
use Morawcik\LunarPayu\Managers\PayuManager;

class LunarPayuPaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {

    }

    public function boot(): void
    {
        Payments::extend('payu', function ($app) {
            return $app->make(PayuPaymentType::class);
        });

        $this->app->singleton('lunar:payu', function ($app) {
            return $app->make(PayuManager::class);
        });

        $this->mergeConfigFrom(__DIR__.'/../config/payu.php', 'lunar.payu');

        $this->publishes([
            __DIR__.'/../config/payu.php' => config_path('lunar/payu.php'),
        ], 'lunar.payu.config');

        Route::group([], function () {
            require __DIR__.'/../routes/web.php';
        });

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'lunar');

        $this->mergeConfigFrom(__DIR__.'/../config/payu.php', 'lunar.payu');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'lunar');

        $this->publishes([
            __DIR__.'/../config/payu.php' => config_path('lunar/payu.php'),
        ], 'lunar.payu.config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/lunar'),
        ], 'lunar.payu.components');

        $this->publishes([
            __DIR__.'/../lang' => lang_path('vendor/lunar'),
        ], 'lunar.payu.translations');

        Livewire::component('payu.payment', PaymentForm::class);
    }
}
