<?php

namespace Webkul\BoldPayment\Payment;

use Webkul\Payment\Payment\Payment;

class BoldPayment extends Payment
{
    /**
     * Payment method code as referenced in configuration.
     *
     * @var string
     */
    protected $code = 'boldpayment';

    /**
     * Redirect URL after placing the order.
     */
    public function getRedirectUrl()
    {
        return route('bold.checkout');
    }

    public function isAvailable()
    {
        return (bool) core()->getConfigData('sales.payment_methods.boldpayment.active');
    }

    public function getTitle()
    {
        return core()->getConfigData('sales.payment_methods.boldpayment.title') ?: 'Bold Payment';
    }

    public function getDescription()
    {
        return core()->getConfigData('sales.payment_methods.boldpayment.description') ?: 'Paga de forma segura con Bold.';
    }
}
