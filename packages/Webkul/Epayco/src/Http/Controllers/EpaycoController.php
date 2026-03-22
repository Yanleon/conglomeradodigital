<?php

namespace Webkul\Epayco\Http\Controllers;

use App\Http\Controllers\Controller;

use Webkul\Checkout\Facades\Cart;
use Webkul\Epayco\Helpers\Ipn;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class EpaycoController extends Controller
{
    public function __construct(
        protected OrderRepository $orderRepository,
        protected Ipn $ipnHelper
    ) {}
    //



    public function setOrder(Request $request){
        return $this->buildRequestBody($this->createOrder()->id);
    }

    public function createOrder(){
        $cart = Cart::getCart();
        $data = (new OrderResource($cart))->jsonSerialize();

        $order = $this->orderRepository->create($data);

        // Mantiene el carrito activo para que el checkout no se quede vacío
        if ($cart) {
            $cart->is_active = 1;
            $cart->save();
        }

        return $order;
    }

    public function buildRequestBody($orderId)
    {
        // Obtiene el carrito
        $cart = Cart::getCart();

        if (! $cart || ! $cart->items || $cart->items->isEmpty()) {
            return response()->json([
                'message' => 'El carrito está vacío. Agrega productos antes de pagar con ePayco.',
            ], 422);
        }

        $billingAddress = $cart->billing_address;
        $country = strtoupper($billingAddress?->country ?? 'CO');
        $typeDoc = null;

        if (is_array($billingAddress?->additional)) {
            $typeDoc = $billingAddress->additional['document_type']
                ?? $billingAddress->additional['type_doc']
                ?? null;
        }

        // Obtiene la informacion de la configuracion de epayco
        $url_response = core()->getConfigData('sales.payment_methods.epayco.url_response');
        $url_confirmation = core()->getConfigData('sales.payment_methods.epayco.url_confirmation');
        $name_store = core()->getConfigData('sales.payment_methods.epayco.name_store');
        $testing_mode = (bool) core()->getConfigData('sales.payment_methods.epayco.testing_mode');
        $public_key = core()->getConfigData('sales.payment_methods.epayco.public_key');
        $private_key = core()->getConfigData('sales.payment_methods.epayco.p_key');

        if (! $public_key || ! $private_key) {
            return response()->json([
                'message' => 'Faltan las llaves de ePayco (public_key / p_key) en configuración.',
            ], 422);
        }

        if (! $url_response || ! $url_confirmation) {
            return response()->json([
                'message' => 'Faltan las URLs de respuesta/confirmación en la configuración de ePayco.',
            ], 422);
        }

        if ((float) $cart->grand_total <= 0) {
            return response()->json([
                'message' => 'El monto del carrito es 0. Verifica los totales antes de pagar.',
            ], 422);
        }

        try {
            $authToken = $this->authenticate($public_key, $private_key);

            $sessionId = $this->createCheckoutSession(
                $authToken,
                [
                    'checkout_version' => '2',
                    'name' => $name_store,
                    'currency' => strtoupper($cart->cart_currency_code ?? 'COP'),
                    'amount' => round((float) $cart->grand_total, 2),
                    'description' => $cart->items?->pluck('name')->implode(', '),
                    'lang' => 'ES',
                    'country' => $country,
                    'invoice' => (string) $orderId,
                    'response' => $url_response,
                    'confirmation' => $url_confirmation,
                    'method' => 'POST',
                    'billing' => array_filter([
                        'email' => $cart->customer_email,
                        'name' => trim(($cart->customer_first_name ?? '') . ' ' . ($cart->customer_last_name ?? '')),
                        'address' => $cart->billing_address->address ?? null,
                        'typeDoc' => $typeDoc,
                        'numberDoc' => $cart->billing_address->vat_id ?? null,
                        'mobilePhone' => $cart->billing_address->phone ?? null,
                        'callingCode' => '+57',
                    ]),
                ]
            );

            return response()->json([
                'sessionId' => $sessionId,
                'test' => $testing_mode,
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => 'No fue posible iniciar el pago con ePayco',
                'error' => $exception->getMessage(),
            ], 422);
        }

    }

    protected function authenticate(string $publicKey, string $privateKey): string
    {
        $credentials = base64_encode($publicKey . ':' . $privateKey);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . $credentials,
        ])->post('https://apify.epayco.co/login');

        if (! $response->ok() || empty($response->json('token'))) {
            $detail = $response->json('textResponse') ?? $response->body();
            throw new \RuntimeException('Error al autenticar con ePayco: ' . ($detail ?: 'sin detalle'));
        }

        return $response->json('token');
    }

    protected function createCheckoutSession(string $token, array $payload): string
    {
        $response = Http::withToken($token)
            ->acceptJson()
            ->post('https://apify.epayco.co/payment/session/create', $payload);

        if (! $response->ok() || empty($response->json('data.sessionId'))) {
            $detail = $response->json('textResponse')
                ?? $response->json('message')
                ?? $response->body();

            throw new \RuntimeException('No se pudo crear la sesión en ePayco: ' . ($detail ?: 'sin detalle'));
        }

        return $response->json('data.sessionId');
    }

    public function ipn(){
        return $this->ipnHelper->processIpn(request()->all());
    }

    public function success(Request $request){
        $refPayco = $request->query('ref_payco');

        if (! $refPayco) {
            session()->flash('error', 'Pago de ePayco sin referencia.');
            return redirect()->route('shop.checkout.cart.index');
        }

        try {
            $response = Http::acceptJson()
                ->timeout(10)
                ->get("https://secure.epayco.co/validation/v1/reference/{$refPayco}");
        } catch (\Throwable $exception) {
            session()->flash('error', 'No fue posible validar el pago en ePayco.');
            return redirect()->route('shop.checkout.cart.index');
        }

        if (! $response->ok()) {
            session()->flash('error', 'No fue posible validar el pago en ePayco.');
            return redirect()->route('shop.checkout.cart.index');
        }

        $resp = $response->json();

        if (! ($resp['status'] ?? false) || empty($resp['data'])) {
            session()->flash('error', 'No se encontró información del pago en ePayco.');
            return redirect()->route('shop.checkout.cart.index');
        }

        $data = $resp['data'];
        $code = (int) ($data['x_cod_response'] ?? 0);

        $statusMap = [
            1 => ['type' => 'success', 'message' => 'Transacción aceptada', 'route' => 'shop.checkout.onepage.success'],
            2 => ['type' => 'error', 'message' => 'Transacción rechazada', 'route' => 'shop.checkout.cart.index'],
            3 => ['type' => 'warning', 'message' => 'Transacción pendiente de confirmación', 'route' => 'shop.checkout.cart.index'],
            4 => ['type' => 'error', 'message' => 'Transacción fallida', 'route' => 'shop.checkout.cart.index'],
        ];

        $status = $statusMap[$code] ?? ['type' => 'error', 'message' => 'No fue posible validar el estado del pago.', 'route' => 'shop.checkout.cart.index'];

        if ($code === 1 && ! empty($data['x_id_invoice'])) {
            session()->flash('order_id', $data['x_id_invoice']);
        }

        session()->flash($status['type'], $status['message']);

        return redirect()->route($status['route']);
    }

}
