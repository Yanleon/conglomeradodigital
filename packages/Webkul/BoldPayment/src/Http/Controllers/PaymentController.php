<?php

namespace Webkul\BoldPayment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class PaymentController extends Controller
{
    public function checkout(Request $request)
    {
        $themes = app('themes');

        if (! $themes->current()) {
            $themes->set(config('themes.shop-default'));
        }

        $cart = function_exists('cart') ? cart()->getCart() : null;

        $orderId = $request->input('order_id');
        $amount = $request->input('amount');
        $currency = $request->input('currency');
        $description = $request->input('description');
        $originUrl = str_replace('127.0.0.1', 'localhost', $request->input('origin_url', url()->current()));
        $renderMode = $request->input('render_mode');
        $customerData = $request->input('customer_data');
        $billingAddress = $request->input('billing_address');
        $extraData1 = $request->input('extra_data_1');
        $extraData2 = $request->input('extra_data_2');
        $tax = $request->input('tax');
        $expirationDate = $request->input('expiration_date');

        if ($cart) {
            $orderId = $orderId ?: 'ORDER_' . $cart->id;
            $amount = $amount !== null ? (int) $amount : (int) round($cart->grand_total);
            $currency = $currency ?: $cart->cart_currency_code;
            $description = $description ?: 'Pago de pedido #' . $cart->id;
        } else {
            $orderId = $orderId ?: 'ORDER_' . now()->timestamp;
            $amount = $amount !== null ? (int) $amount : 0;
            $currency = $currency ?: 'COP';
            $description = $description ?: 'Pago con Bold';
        }

        $currency = strtoupper($currency);
        $redirectUrl = str_replace('127.0.0.1', 'localhost', $request->input('redirect_url', route('bold.callback')));
        // Usar Embedded Checkout por defecto según la guía.
        $renderMode = $renderMode ?: 'embedded';

        // Normalizar datos anidados según documentación (cadenas JSON en camelCase).
        if (is_array($customerData)) {
            $customerData = json_encode($customerData);
        }

        if (is_array($billingAddress)) {
            $billingAddress = json_encode($billingAddress);
        }

        $apiKey = core()->getConfigData('sales.payment_methods.boldpayment.api_key');
        $secretKey = core()->getConfigData('sales.payment_methods.boldpayment.secret_key');

        $signature = null;

        if ($secretKey) {
            // Bold concatena orderId + amount (string) + currency + secretKey
            $signature = hash('sha256', "{$orderId}" . (string) $amount . "{$currency}{$secretKey}");
        }

        $config = array_filter([
            'orderId'            => $orderId,
            'amount'             => (string) $amount,
            'currency'           => $currency,
            'description'        => $description,
            'redirectionUrl'     => $redirectUrl,
            'renderMode'         => $renderMode,
            'originUrl'          => $originUrl,
            'customerData'       => $customerData,
            'billingAddress'     => $billingAddress,
            'extraData1'         => $extraData1,
            'extraData2'         => $extraData2,
            'tax'                => $tax,
            'expirationDate'     => $expirationDate,
            'apiKey'             => $apiKey,
            'integritySignature' => $signature,
        ], function ($value) {
            return ! is_null($value) && $value !== '';
        });

        if (empty($apiKey)) {
            abort(500, 'Falta la API Key de Bold en la configuración.');
        }

        if (empty($signature)) {
            abort(500, 'Falta la llave secreta de Bold para generar la firma de integridad.');
        }

        return view('boldpayment::checkout', [
            'orderId'        => $orderId,
            'amount'         => $amount,
            'currency'       => $currency,
            'description'    => $description,
            'redirectUrl'    => $redirectUrl,
            'renderMode'     => $renderMode,
            'config'         => $config,
            'buttonLabel'    => $request->input('button_label', 'Pagar ahora'),
        ]);
    }

    public function callback(Request $request)
    {
        $orderId = $request->query('bold-order-id');
        $status = $request->query('bold-tx-status');
        $params = [
            'order_id' => $orderId,
            'status'   => $status,
        ];

        if (Route::has('shop.checkout.success')) {
            return redirect()->route('shop.checkout.success', $params);
        }

        $query = http_build_query([
            'bold-order-id'  => $orderId,
            'bold-tx-status' => $status,
        ]);

        return redirect()->to(url('/').($query ? "?{$query}" : ''));
    }

    public function generateSignature(Request $request)
    {
        $data = $request->validate([
            'reference' => 'nullable|string|max:60',
            'order_id'  => 'nullable|string|max:60',
            'amount'    => 'required|integer|min:0',
            'currency'  => 'required|string|size:3',
        ]);

        $orderId = $data['order_id'] ?? $data['reference'];

        if (! $orderId) {
            return response()->json([
                'message' => 'Debes enviar order_id o reference para generar la firma.',
            ], 422);
        }

        $secretKey = core()->getConfigData('sales.payment_methods.boldpayment.secret_key');

        if (! $secretKey) {
            return response()->json([
                'message' => 'Configura la llave secreta de Bold para generar firmas.',
            ], 422);
        }

        $payload = sprintf('%s%s%s%s', $orderId, $data['amount'], strtoupper($data['currency']), $secretKey);

        return response()->json([
            'signature' => hash('sha256', $payload),
        ]);
    }
}
