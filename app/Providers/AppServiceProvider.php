<?php

namespace App\Providers;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\Payment;
use App\Observers\DeliveryObserver;
use App\Observers\OrderObserver;
use App\Observers\PaymentObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Order::observe(OrderObserver::class);
        Payment::observe(PaymentObserver::class);
        Delivery::observe(DeliveryObserver::class);
    }
}
