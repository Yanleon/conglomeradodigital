<?php

namespace Webkul\Epayco\Http\Controllers;

use App\Http\Controllers\Controller;
use Webkul\Checkout\Facades\Cart;
use Webkul\Epayco\Helpers\Ipn;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EpaycoController extends Controller
{
    public function __construct(
        protected OrderRepository $orderRepository,
        protected Ipn $ipnHelper
    ) {}

    public function setOrder()
    {
        $order = $this->createOrder();
        return $this->buildRequestBody($order->id);
    }

    public function createOrder()
    {
        $cart = Cart::getCart();
        $data = (new OrderResource($cart))->jsonSerialize();

        return $this->orderRepository->create($data);
    }

    public function buildRequestBody($orderId)
    {
        $cart = Cart::getCart();
        $billing = $cart->billing_address;
        $customer = $cart->customer;

        // 🔥 VALIDACIONES (CLAVE)
        if (!$billing) {
            return response()->json(['error' => 'Debe ingresar dirección de facturación'], 400);
        }

        if (empty($cart->customer_email)) {
            return response()->json(['error' => 'Email requerido'], 400);
        }

        if (empty($billing->phone)) {
            return response()->json(['error' => 'Teléfono requerido'], 400);
        }

        if (empty($cart->customer_first_name)) {
            return response()->json(['error' => 'Nombre requerido'], 400);
        }

        if (empty($cart->grand_total) || $cart->grand_total <= 0) {
            return response()->json(['error' => 'Total inválido'], 400);
        }

        // Configuración
        $url_response = core()->getConfigData('sales.payment_methods.epayco.url_response');
        $url_confirmation = core()->getConfigData('sales.payment_methods.epayco.url_confirmation');
        $name_store = core()->getConfigData('sales.payment_methods.epayco.name_store');
        $testing_mode = core()->getConfigData('sales.payment_methods.epayco.testing_mode');

        // Desactiva carrito
        Cart::deActivateCart();

        // Dirección segura (array o string)
        $address = is_array($billing->address)
            ? implode(' ', $billing->address)
            : $billing->address;

        // 🔥 PAYLOAD CORRECTO
        $data = [
            'name' => $name_store . '#' . $orderId,
            'description' => core()->getConfigData('sales.payment_methods.epayco.description') ?? 'Pedido #' . $orderId,
            'invoice' => $orderId,

            'currency' => 'COP',
            'amount' => $cart->grand_total,

            'tax_base' => '0',
            'tax' => '0',

            'country' => 'co',
            'lang' => 'es',

            'external' => false,
            'test' => $testing_mode ? true : false,

            'response' => $url_response,
            'confirmation' => $url_confirmation,

            // 👇 DATOS REALES DEL CLIENTE
            'name_billing' => $cart->customer_first_name . ' ' . $cart->customer_last_name,
            'email_billing' => $cart->customer_email,
            'mobilephone_billing' => $billing->phone,
            'address_billing' => $address,

            'type_doc_billing' => 'CC',
            'number_doc_billing' => $customer->id ?? '0',
        ];

        // Log para debug
        Log::info('EPAYCO DATA', $data);

        return response()->json($data);
    }

    public function ipn()
    {
        return $this->ipnHelper->processIpn(request()->all());
    }

    public function success(Request $request)
    {
        $ref_payco = $request->query('ref_payco');

        try {
            $testing_mode = core()->getConfigData('sales.payment_methods.epayco.testing_mode');
            $baseUrl = $testing_mode
                ? 'https://testopayments.epayco.co'
                : 'https://secure.epayco.co';

            $client = new Client();
            $response = $client->get($baseUrl . '/validation/v1/reference/' . $ref_payco);

            $resp = json_decode($response->getBody()->getContents(), true);

            if (isset($resp["status"])) {
                Log::warning('Epayco validation failed', ['ref' => $ref_payco, 'resp' => $resp]);
                return redirect()->route('shop.checkout.cart.index')
                    ->with('error', 'Payment validation failed.');
            }

            session()->flash('order_id', $resp["data"]["x_id_invoice"]);

            return redirect()->route('shop.checkout.onepage.success');

        } catch (RequestException $e) {
            Log::error('Epayco API error', ['error' => $e->getMessage()]);
            return redirect()->route('shop.checkout.cart.index')
                ->with('error', 'Payment verification failed.');

        } catch (\Exception $e) {
            Log::error('Epayco general error', ['error' => $e->getMessage()]);
            return redirect()->route('shop.checkout.cart.index')
                ->with('error', 'Error inesperado.');
        }
    }
}