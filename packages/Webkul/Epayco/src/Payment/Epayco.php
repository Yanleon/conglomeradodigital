<?php

namespace Webkul\Epayco\Payment;

use Webkul\Payment\Payment\Payment;

class Epayco extends Payment
{
    protected $code = 'epayco';


    public function getRedirectUrl()
    {
        return route('epayco.standard.set-order');
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function process()
    {
        // Handled by JS checkout, no server-side process needed
        return true;
    }
}

