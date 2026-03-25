<?php

namespace Webkul\Epayco\Payment;

use Webkul\Payment\Payment\Payment;
use Illuminate\Support\Facades\Storage;

class Epayco extends Payment
{
    /**
     * Payment method code
     *
     * @var string
     */
    protected $code  = 'epayco';

    public function getRedirectUrl()
    {

    }

    public function getImage()
    {
        $url = $this->getConfigData('image');

        return $url ? Storage::url($url) : bagisto_asset('images/epayco.png', 'shop');
    }
}
