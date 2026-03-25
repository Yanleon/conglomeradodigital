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

    /**
     *  SOLO PREPARA DATOS PARA EPAYCO
     */
    public function setOrder()
    {
        return $this->buildRequestBody();
    }

    /**
     *  CREA ORDEN
     */
    public function createOrder()
    {
        $cart = Cart::getCart();
        $data = (new OrderResource($cart))->jsonSerialize();

        return $this->orderRepository->create($data);
    }

    /**
     * PAYLOAD EPAYCO
     */
    public function buildRequestBody()
    {
        $cart = Cart::getCart();
        $billing = $cart->billing_address;
        $customer = $cart->customer;

        // VALIDACIONES
        if (!$billing) {
            return response()->json(['error' => 'Debe ingresar dirección'], 400);
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

        // Dirección segura
        $address = is_array($billing->address)
            ? implode(' ', $billing->address)
            : $billing->address;

        // REFERENCIA ÚNICA
$order = $this->createOrder();
session(['epayco_order_id' => $order->id]);
Log::info('Epayco order created early', ['order_id' => $order->id]);
$invoice = $order->id;

        $data = [
            'name' => $name_store . '#' . $invoice,
            'description' => 'Compra en tienda',
            'invoice' => $invoice,
            'extra1' => $order->id, // Bagisto order ID for IPN

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

            // Cliente
            'name_billing' => trim($cart->customer_first_name . ' ' . $cart->customer_last_name),
            'email_billing' => $cart->customer_email,
            'mobilephone_billing' => preg_replace('/\D/', '', $billing->phone),
            'address_billing' => $address,

            'type_doc_billing' => 'CC',
            'number_doc_billing' => $customer->id ?? '123456789',
        ];

        Log::info('EPAYCO DATA', $data);

        $responseData = array_merge($data, ['bagisto_order_id' => $order->id]);

        return response()->json($responseData);
    }

    /**
     * IPN
     */
    public function ipn()
    {
        return $this->ipnHelper->processIpn(request()->all());
    }

    
    public function success(Request $request)
    {
        $ref_payco = $request->query('ref_payco');

        if (!$ref_payco) {
            Log::error('No llegó ref_payco');
            return redirect()->route('shop.checkout.cart.index')
                ->with('error', 'Error en el pago');
        }

        try {
            $testing_mode = core()->getConfigData('sales.payment_methods.epayco.testing_mode');

            $baseUrl = $testing_mode
                ? 'https://testopayments.epayco.co'
                : 'https://secure.epayco.co';

            $client = new Client();

            $response = $client->get($baseUrl . '/validation/v1/reference/' . $ref_payco);

            $resp = json_decode($response->getBody()->getContents(), true);

            Log::info('EPAYCO RESPONSE', $resp);

            $orderId = session('epayco_order_id');
            if ($orderId) {
                $order = $this->orderRepository->find($orderId);
                if (!$order) {
                    Log::warning('Epayco session order not found, creating fallback');
                    $order = $this->createOrder();
                }
            } else {
                $order = $this->createOrder();
            }
            session()->forget('epayco_order_id');
            Log::info('Epayco success using order', ['order_id' => $order->id]);

            if (!isset($resp["data"]["x_cod_response"]) || $resp["data"]["x_cod_response"] != 1) {

                Log::warning('Pago no aprobado', $resp);

                // Eliminar la orden para que no aparezca en ventas cuando es rechazada/fallida
                $this->orderRepository->delete($order->id);

                return redirect()->route('shop.checkout.cart.index')
                    ->with('error', 'Pago no aprobado');
            }

            $this->orderRepository->update([
                'status' => 'processing',
                'transaction_id' => $resp["data"]["x_ref_payco"] ?? $ref_payco
            ], $order->id);

            Cart::deActivateCart();

            session()->flash('order_id', $order->id);

            return redirect()->route('shop.checkout.onepage.success');

        } catch (RequestException $e) {

            Log::error('Epayco API error', ['error' => $e->getMessage()]);

            return redirect()->route('shop.checkout.cart.index')
                ->with('error', 'Error verificando pago');

        } catch (\Exception $e) {

            Log::error('Epayco general error', ['error' => $e->getMessage()]);

            return redirect()->route('shop.checkout.cart.index')
                ->with('error', 'Error inesperado');
        }
    }
}
