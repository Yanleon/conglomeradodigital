<?php

namespace Webkul\Epayco\Http\Controllers;

use App\Http\Controllers\Controller;
use Webkul\Checkout\Facades\Cart;
use Webkul\Epayco\Helpers\Ipn;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;
use Webkul\Epayco\Services\EpaycoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EpaycoController extends Controller
{
    public function __construct(
        protected OrderRepository $orderRepository,
        protected Ipn $ipnHelper,
        protected EpaycoService $epaycoService
    ) {}

    public function setOrder()
    {
        try {
            $payload = $this->epaycoService->buildPayload();
            return response()->json($payload);
        } catch (\Exception $e) {
            Log::error('Epayco setOrder error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function ipn()
    {
        return $this->ipnHelper->processIpn(request()->all());
    }

    public function success(Request $request)
    {
        $ref_payco = $request->query('ref_payco');

        if (!$ref_payco) {
            return redirect()->route('shop.checkout.cart.index')
                ->with('error', 'Error en el pago');
        }

        try {
            $charge = $this->epaycoService->verifyCharge($ref_payco);

            if (!$charge) {
                return redirect()->route('shop.checkout.cart.index')
                    ->with('error', 'Error verificando pago');
            }

            Log::info('EPAYCO RESPONSE RAW', $charge);

            $codResponse = $charge['data']['x_cod_response'] ?? '0';

            if ((string)$codResponse !== '1') {
                return redirect()->route('shop.checkout.cart.index')
                    ->with('error', 'Pago no aprobado');
            }

            $cart = Cart::getCart();

            $order = $this->orderRepository->create(
                (new OrderResource($cart))->jsonSerialize()
            );

            $this->orderRepository->update([
                'status' => 'completed',
                'transaction_id' => $charge['data']['x_ref_payco'] ?? $ref_payco
            ], $order->id);

            Cart::deActivateCart();

            session()->flash('order_id', $order->id);

            return redirect()->route('shop.checkout.onepage.success');

        } catch (\Exception $e) {
            Log::error('Epayco success error: ' . $e->getMessage());

            return redirect()->route('shop.checkout.cart.index')
                ->with('error', 'Error verificando pago');
        }
    }
}