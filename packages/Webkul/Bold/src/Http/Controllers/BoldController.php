<?php

namespace Webkul\Bold\Http\Controllers;

use Webkul\Checkout\Facades\Cart;
use Illuminate\Http\Request;

class BoldController extends Controller
{
    public function redirect()
    {
        $cart = Cart::getCart();
        $total = $cart->grand_total;

        $publicKey = core()->getConfigData('sales.paymentmethods.bold.public_key');

        return view('bold::components.bold-smart-button', compact('total', 'publicKey'));
    }

    public function notify(Request $request)
    {
        // Aquí procesas la notificación o confirmación de pago de Bold
    }
}
