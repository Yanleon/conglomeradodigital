<?php

namespace Webkul\Bold\Payment;

use Webkul\Payment\Payment\Payment;

class Bold extends Payment
{
    protected $code = 'bold';

    public function getRedirectUrl()
    {
        // Puedes personalizar el flujo de redirección aquí si tu método usa API externa
        return route('shop.checkout.success');
    }
}
