<?php

namespace Webkul\BoldPayment\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Webkul\BoldPayment\Payment\BoldPayment;

class BoldPaymentServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../Http/routes.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'boldpayment');
        $this->mergeConfigFrom(__DIR__ . '/../Config/system.php', 'core');
        $this->mergeConfigFrom(__DIR__ . '/../Config/paymentmethods.php', 'payment_methods');

        Event::listen('checkout.payment-methods.before', function ($methods) {
            $methods->push([
                'method'             => 'boldpayment',
                'method_title'       => core()->getConfigData('sales.payment_methods.boldpayment.title') ?: 'Bold Payment',
                'method_description' => core()->getConfigData('sales.payment_methods.boldpayment.description'),
                'sort'               => core()->getConfigData('sales.payment_methods.boldpayment.sort') ?: 3,
                'class'              => BoldPayment::class,
            ]);
        });
    }

    public function register()
    {
        //
    }
}
