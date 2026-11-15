<?php

namespace App\Providers;

use App\Models\Category;
use App\Payments\BkashSandbox;
use App\Payments\DemoGateway;
use App\Payments\PaymentGateway;
use App\Services\Cart;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // PAYMENT_DRIVER=demo → demo page on this site; anything else → bKash sandbox (docs §1.8)
        $this->app->bind(PaymentGateway::class, fn ($app) => config('services.payment.driver') === 'demo'
            ? $app->make(DemoGateway::class)
            : $app->make(BkashSandbox::class));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('pagination');
        Paginator::defaultSimpleView('pagination');

        View::composer('components.header', function ($view): void {
            $view->with([
                'navCategories' => Category::active()->orderBy('sort_order')->get(),
                'cartCount' => $this->app->make(Cart::class)->count(),
            ]);
        });
    }
}
