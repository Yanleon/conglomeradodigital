<?php

namespace Webkul\BoldPayment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;

class PaymentController extends Controller
{
    public function __construct(
        protected OrderRepository $orderRepository,
        protected InvoiceRepository $invoiceRepository
    ) {}

    public function checkout(Request $request)
    {
        $themes = app('themes');

        if (! $themes->current()) {
            $themes->set(config('themes.shop-default'));
        }

        $cart = function_exists('cart') ? cart()->getCart() : null;

        if (! $cart) {
            abort(400, 'No hay carrito activo para procesar el pago.');
        }

        $orderId = $request->input('order_id') ?: session('bold_order_id');

        $rawAmount = $request->input('amount');
        if (is_string($rawAmount)) {
            $rawAmount = preg_replace('/[^0-9]/', '', $rawAmount);
        }
        $amount = $rawAmount;

        $currency = $request->input('currency');
        $description = $request->input('description');
        $originUrl = str_replace('127.0.0.1', 'localhost', $request->input('origin_url', url()->current()));
        $originUrl = str_replace('http://', 'https://', $originUrl);
        $renderMode = $request->input('render_mode');
        $customerData = $request->input('customer_data');
        $billingAddress = $request->input('billing_address');
        $extraData1 = $request->input('extra_data_1');
        $extraData2 = $request->input('extra_data_2');
        $tax = $request->input('tax');
        $expirationDate = $request->input('expiration_date');

        if ($cart) {
            $orderId = $orderId ?: 'BOLD-' . $cart->id . '-' . now()->timestamp;
            session()->put('bold_order_id', $orderId);
            $amount = $amount !== null ? (int) $amount : (int) round($cart->grand_total);
            $currency = $currency ?: $cart->cart_currency_code;
            $description = $description ?: 'Pago de pedido #' . $cart->id;
        }

        if ($amount < 1000) {
            abort(422, 'El monto debe ser mayor o igual a 1000 (COP).');
        }

        $currency = strtoupper($currency);

        $defaultRedirect = route('bold.callback');
        $defaultRedirect = str_replace('127.0.0.1', 'localhost', $defaultRedirect);
        $defaultRedirect = str_replace('http://', 'https://', $defaultRedirect);

        $redirectUrl = str_replace('127.0.0.1', 'localhost', $request->input('redirect_url', $defaultRedirect));
        $redirectUrl = str_replace('http://', 'https://', $redirectUrl);
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

        Log::info('[Bold] Checkout config', [
            'orderId'     => $orderId,
            'amount'      => $amount,
            'currency'    => $currency,
            'hasApiKey'   => ! empty($apiKey),
            'hasSecret'   => ! empty($secretKey),
            'redirectUrl' => $redirectUrl,
            'originUrl'   => $originUrl,
            'renderMode'  => $renderMode,
        ]);

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

        if ($status === 'approved' && $orderId) {
            try {
                $order = $this->createOrderFromCart($orderId);
                $params['created_order_id'] = $order?->increment_id ?? null;
                $this->markOrderPaid($order->increment_id);
            } catch (\Exception $e) {
                Log::error('[Bold] Error al procesar orden tras callback', [
                    'orderId' => $orderId,
                    'message' => $e->getMessage(),
                ]);
            }
        } elseif ($orderId) {
            try {
                $this->forgetBoldSession();
            } catch (\Exception $e) {
                Log::error('[Bold] Error al limpiar sesión tras callback', [
                    'orderId' => $orderId,
                    'message' => $e->getMessage(),
                ]);
            }
        }

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

    public function config(Request $request)
    {
        $cart = Cart::getCart();

        if (! $cart) {
            return response()->json(['message' => 'No hay carrito activo'], 404);
        }

        $this->validateOrder();
        Cart::collectTotals();

        $orderId = $this->getOrderId();
        $amount = (int) round($cart->grand_total);
        $currency = strtoupper($cart->cart_currency_code ?: 'COP');
        $description = 'Pago con Bold';
        $originUrl = url()->current();

        $apiKey = core()->getConfigData('sales.payment_methods.boldpayment.api_key');
        $secretKey = core()->getConfigData('sales.payment_methods.boldpayment.secret_key');

        if (empty($apiKey) || empty($secretKey)) {
            return response()->json(['message' => 'Faltan llaves de Bold en configuración'], 500);
        }

        $signature = hash('sha256', "{$orderId}{$amount}{$currency}{$secretKey}");

        return response()->json([
            'orderId'            => $orderId,
            'amount'             => (string) $amount,
            'currency'           => $currency,
            'description'        => $description,
            'redirectionUrl'     => route('bold.callback'),
            'renderMode'         => 'embedded',
            'originUrl'          => $originUrl,
            'apiKey'             => $apiKey,
            'integritySignature' => $signature,
            'buttonStyle'        => core()->getConfigData('sales.payment_methods.boldpayment.button_style') ?: 'dark-L',
        ]);
    }

    protected function getOrderId(): string
    {
        $existing = session('bold_order_id');

        if ($existing) {
            return $existing;
        }

        $cart = Cart::getCart();
        $orderId = 'BOLD-' . ($cart?->id ?: now()->timestamp) . '-' . now()->timestamp;
        session()->put('bold_order_id', $orderId);

        return $orderId;
    }

    protected function createOrderFromCart(string $incrementId)
    {
        if (Cart::hasError()) {
            throw new \Exception('El carrito tiene errores, no se puede crear la orden.');
        }

        Cart::collectTotals();
        $this->validateOrder();

        $cart = Cart::getCart();

        $data = (new OrderResource($cart))->jsonSerialize();
        $data['increment_id'] = $incrementId;

        $order = $this->orderRepository->create($data);

        return $order;
    }

    protected function markOrderPaid($incrementId)
    {
        $order = $this->orderRepository->findOneByField('increment_id', $incrementId);

        if (! $order) {
            throw new \Exception('Orden no encontrada para increment_id: '.$incrementId);
        }

        $this->orderRepository->update(['status' => 'completed'], $order->id);

        if ($order->canInvoice()) {
            $this->invoiceRepository->create($this->prepareInvoiceData($order));
        }

        Cart::deActivateCart();
        $this->forgetBoldSession();
    }

    protected function prepareInvoiceData($order)
    {
        $invoiceData = ['order_id' => $order->id];

        foreach ($order->items as $item) {
            $invoiceData['invoice']['items'][$item->id] = $item->qty_to_invoice;
        }

        return $invoiceData;
    }

    protected function forgetBoldSession(): void
    {
        session()->forget('bold_order_id');
    }

    protected function validateOrder()
    {
        $cart = Cart::getCart();

        $minimumOrderAmount = (float) core()->getConfigData('sales.order_settings.minimum_order.minimum_order_amount') ?: 0;

        if (! Cart::haveMinimumOrderAmount()) {
            throw new \Exception(trans('shop::app.checkout.cart.minimum-order-message', ['amount' => core()->currency($minimumOrderAmount)]));
        }

        if (
            $cart->haveStockableItems()
            && ! $cart->shipping_address
        ) {
            throw new \Exception(trans('shop::app.checkout.cart.check-shipping-address'));
        }

        if (! $cart->billing_address) {
            throw new \Exception(trans('shop::app.checkout.cart.check-billing-address'));
        }

        if (
            $cart->haveStockableItems()
            && ! $cart->selected_shipping_rate
        ) {
            throw new \Exception(trans('shop::app.checkout.cart.specify-shipping-method'));
        }

        if (! $cart->payment) {
            throw new \Exception(trans('shop::app.checkout.cart.specify-payment-method'));
        }
    }
}
