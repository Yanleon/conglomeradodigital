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
     * 🔥 SOLO PREPARA DATOS PARA EPAYCO
     */
    public function setOrder()
    {
        return $this->buildRequestBody();
    }

    /**
     * 🔥 CREA ORDEN
     */
    public function createOrder()
    {
        $cart = Cart::getCart();
        $data = (new OrderResource($cart))->jsonSerialize();

        return $this->orderRepository->create($data);
    }

    /**
     * 🔥 PAYLOAD EPAYCO
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

        // 🔥 REFERENCIA ÚNICA (usamos el carrito para no crear orden aún)
        $reference = (string) ($cart->id ?? time());
        session([
            'epayco_cart_id' => $cart->id,
            'epayco_reference' => $reference,
        ]);
        Log::info('Epayco checkout reference created', ['reference' => $reference]);
        $invoice = $reference;

        // Documento en formato string solo con dígitos
        $rawDocument = $billing->vat_id ?? ($customer ? $customer->id : null);
        $documentNumber = preg_replace('/\D/', '', (string) ($rawDocument ?? ''));
        $documentNumber = $documentNumber !== '' ? $documentNumber : '123456789';

        $data = [
            'name' => $name_store . '#' . $invoice,
            'description' => 'Compra en tienda',
            'invoice' => $invoice,
            'extra1' => $reference,

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
            'number_doc_billing' => $documentNumber,
        ];

        Log::info('EPAYCO DATA', $data);

        return response()->json($data);
    }

    /**
     * IPN
     */
    public function ipn()
    {
        return $this->ipnHelper->processIpn(request()->all());
    }

    /**
     * 🔥 SUCCESS (CREA ORDEN Y ACTUALIZA ESTADO)
     */
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

            // Reactiva el carrito si no está cargado (evita perder la venta en reintentos)
            $cart = Cart::getCart();
            if (! $cart && session()->has('epayco_cart_id')) {
                Cart::activateCart((int) session('epayco_cart_id'));
                $cart = Cart::getCart();
            }

            if (! $cart) {
                Log::error('Epayco success sin carrito activo');
                return redirect()->route('shop.checkout.cart.index')
                    ->with('error', 'No se pudo recuperar tu carrito, por favor intenta de nuevo.');
            }

            $codResponse = (int) ($resp["data"]["x_cod_response"] ?? 0);

            // ❌ Pago no aprobado / pendiente
            if ($codResponse !== 1) {

                $message = 'Pago no aprobado';

                if ($codResponse === 3) {
                    $message = 'Pago pendiente, verifica con tu banco o intenta de nuevo';
                    Log::warning('Pago pendiente', $resp);
                } else {
                    Log::warning('Pago no aprobado', $resp);
                }

                session()->forget(['epayco_cart_id', 'epayco_reference']);

                return redirect()->route('shop.checkout.cart.index')
                    ->with('error', $message);
            }

            // ✅ Pago aprobado
            $order = $this->createOrder();
            Log::info('Epayco success creating order after approval', ['order_id' => $order->id]);

            $this->orderRepository->update([
                'status' => 'processing',
                'transaction_id' => $resp["data"]["x_ref_payco"] ?? $ref_payco
            ], $order->id);

            Cart::deActivateCart();

            session()->flash('order_id', $order->id);

            session()->forget(['epayco_cart_id', 'epayco_reference']);

            return redirect()->route('shop.home.index')
                ->with('success', 'Pago aprobado');

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
