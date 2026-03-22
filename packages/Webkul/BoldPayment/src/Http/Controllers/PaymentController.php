<?php

namespace Webkul\BoldPayment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function checkout(Request $request)
    {
        $cart = function_exists('cart') ? cart()->getCart() : null;

        $orderId = $request->input('order_id');
        $amount = $request->input('amount');
        $currency = $request->input('currency');
        $description = $request->input('description');

        if ($cart) {
            $orderId = $orderId ?: 'ORDER_' . $cart->id;
            // Bold suele esperar centavos. Pasamos en centavos para mayor compatibilidad.
            $amount = $amount !== null ? (int) $amount : (int) round($cart->grand_total * 100);
            $currency = $currency ?: $cart->cart_currency_code;
            $description = $description ?: 'Pago de pedido #' . $cart->id;
        } else {
            $orderId = $orderId ?: 'ORDER_' . now()->timestamp;
            $amount = $amount !== null ? (int) $amount : 0;
            $currency = $currency ?: 'COP';
            $description = $description ?: 'Pago con Bold';
        }

        $currency = strtoupper($currency);
        $redirectUrl = $request->input('redirect_url', route('bold.callback', absolute: true));
        // Bold espera "embedded" (modal) o "redirect".
        $renderMode = $request->boolean('embedded', true) ? 'embedded' : 'redirect';

        $apiKey = core()->getConfigData('sales.payment_methods.boldpayment.api_key');
        $secretKey = core()->getConfigData('sales.payment_methods.boldpayment.secret_key');
        $merchantId = core()->getConfigData('sales.payment_methods.boldpayment.merchant_id');
        $buttonStyle = core()->getConfigData('sales.payment_methods.boldpayment.button_style') ?: 'dark-M';
        $environment = core()->getConfigData('sales.payment_methods.boldpayment.sandbox') ? 'sandbox' : 'production';

        $signature = null;

        if ($amount > 0) {
            if (! $secretKey) {
                abort(500, 'Falta la llave secreta de Bold en la configuración.');
            }

            $signature = hash('sha256', "{$orderId}{$amount}{$currency}{$secretKey}");
        }

        return view('boldpayment::checkout', [
            'orderId'     => $orderId,
            'amount'      => $amount,
            'currency'    => $currency,
            'description' => $description,
            'redirectUrl' => $redirectUrl,
            'renderMode'  => $renderMode,
            'apiKey'      => $apiKey,
            'signature'   => $signature,
            'buttonStyle' => $buttonStyle,
            'merchantId'  => $merchantId,
            'environment' => $environment,
        ]);
    }

    public function callback(Request $request)
    {
        $orderId = $request->query('bold-order-id');
        $status = $request->query('bold-tx-status');

        return redirect()->route('shop.checkout.success', [
            'order_id' => $orderId,
            'status'   => $status,
        ]);
    }

    public function generateSignature(Request $request)
    {
        $data = $request->validate([
            'reference' => 'required|string|max:60',
            'amount'    => 'required|integer|min:0',
            'currency'  => 'required|string|size:3',
        ]);

        $secretKey = core()->getConfigData('sales.payment_methods.boldpayment.secret_key');

        if (! $secretKey) {
            return response()->json([
                'message' => 'Configura la llave secreta de Bold para generar firmas.',
            ], 422);
        }

        $payload = sprintf('%s%s%s%s', $data['reference'], $data['amount'], strtoupper($data['currency']), $secretKey);

        return response()->json([
            'signature' => hash('sha256', $payload),
        ]);
    }
}
