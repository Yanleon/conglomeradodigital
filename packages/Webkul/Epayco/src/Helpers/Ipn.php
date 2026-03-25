<?php

namespace Webkul\Epayco\Helpers;

use Illuminate\Support\Facades\Log;
use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;

class Ipn
{
    /**
     * IPN post data.
     *
     * @var array
     */
    protected $post;

    /**
     * Order $order
     *
     * @var \Webkul\Sales\Contracts\Order
     */
    protected $order;

    /**
     * Public key.
     *
     * @var string
     */
    protected $public_key;

    /**
     * Cust id client.
     *
     * @var string
     */
    protected $cust_id_client;

    /**
     * P key.
     *
     * @var string
     */
    protected $p_key;

    protected $status_order;

    /**
     * Create a new helper instance.
     *
     * @return void
     */
    public function __construct(
        protected OrderRepository $orderRepository,
        protected InvoiceRepository $invoiceRepository,
    ) {}

    /**
     * This function process the IPN sent from paypal end.
     *
     * @param  array  $post
     * @return null|void|\Exception
     */
    public function processIpn($post)
    {
        $this->post = $post;

        try {
            Log::info('Epayco IPN received', $post);

            $this->public_key = core()->getConfigData('sales.payment_methods.epayco.public_key');
            $this->cust_id_client = core()->getConfigData('sales.payment_methods.epayco.cust_id_client');
            $this->p_key = core()->getConfigData('sales.payment_methods.epayco.p_key');
            $this->getOrder();
            if (!$this->order) {
                Log::error('Epayco IPN: Order not found for x_id_invoice ' . ($this->post['x_id_invoice'] ?? 'unknown'));
                return response()->json(['error' => 'Order not found'], 404);
            }
            return $this->processOrder();

        } catch (\Exception $e) {
            Log::error('Epayco IPN error: ' . $e->getMessage(), $post);
            throw $e;
        }
    }

    /**
     * Load order via IPN invoice id.
     *
     * @return void
     */
    protected function getOrder()
    {
        $invoiceId = $this->post['x_id_invoice'] ?? null;
        $refPayco  = $this->post['x_ref_payco'] ?? null;

        // 1) Buscar por ID de orden (para flujos antiguos)
        if ($invoiceId) {
            $this->order = $this->orderRepository->findOneByField(['id' => $invoiceId]);
        }

        // 2) Buscar por transaction_id (si ya se creó en success)
        if (! $this->order && $refPayco) {
            $this->order = $this->orderRepository->findOneByField(['transaction_id' => $refPayco]);
        }

        // 3) Podríamos crear luego desde el carrito (x_extra1) si está aprobado

        Log::info('Epayco IPN looking for order', [
            'x_id_invoice' => $invoiceId,
            'x_ref_payco' => $refPayco,
            'found' => $this->order ? $this->order->id : null
        ]);

        return $this->order;
    }

    /**
     * Process order and create invoice.
     *
     * @return void
     */
    protected function processOrder()
    {
        if (!$this->order) {
            Log::error('Epayco IPN processOrder called without order');
            return response()->json(['error' => 'No order'], 404);
        }
        Log::info('Epayco IPN processing order', ['order_id' => $this->order->id]);

        $response = [];

        $x_ref_payco      = $this->post['x_ref_payco'];
        $x_transaction_id = $this->post['x_transaction_id'];
        $x_amount         = (int) $this->post['x_amount'];
        $x_currency_code  = $this->post['x_currency_code'];
        $x_signature      = $this->post['x_signature'];
        $x_cod_response   = (int) ($this->post['x_cod_response'] ?? 0);
        $x_extra1_cart_id = $this->post['x_extra1'] ?? null; // cart id que enviamos

        $signature = hash('sha256', $this->cust_id_client . '^' . $this->p_key . '^' . $x_ref_payco . '^' . $x_transaction_id . '^' . $x_amount . '^' . $x_currency_code);

        // Si no hay orden y el pago está aprobado, intentamos crearla desde el carrito
        if (! $this->order && $x_cod_response === 1 && $x_extra1_cart_id) {
            $createdOrder = $this->createOrderFromCartId((int) $x_extra1_cart_id);
            if ($createdOrder) {
                $this->order = $createdOrder;
                Log::info('Epayco IPN creó orden desde carrito', ['order_id' => $this->order->id, 'cart_id' => $x_extra1_cart_id]);
            } else {
                Log::error('Epayco IPN no pudo crear orden desde carrito', ['cart_id' => $x_extra1_cart_id]);
            }
        }

        // Sin orden y pago no aprobado: solo respondemos
        if (! $this->order && $x_cod_response !== 1) {
            Log::warning('Epayco IPN sin orden para estado no aprobado', ['cart_id' => $x_extra1_cart_id, 'cod' => $x_cod_response]);
            return response()->json([
                'alert' => 'error',
                'message' => 'Sin orden asociada para este estado',
                'status' => 'pending_payment'
            ], 202);
        }

        // Si sigue sin orden (caso raro aprobado y sin carrito válido)
        if (! $this->order) {
            Log::error('Epayco IPN no encontró ni creó orden', ['cart_id' => $x_extra1_cart_id, 'cod' => $x_cod_response]);
            return response()->json([
                'alert' => 'error',
                'message' => 'Orden no encontrada'
            ], 404);
        }

        $numOrder   = $this->order->id;
        $valueOrder = (int) round($this->order->grand_total);

        // Se valida firma y montos
        if ($x_signature != $signature) {
            Log::error('Epayco IPN firma inválida', ['order_id' => $numOrder]);
            return response()->json([
                'alert' => 'error',
                'message' => 'Firma inválida'
            ], 400);
        }

        if ($x_amount !== $valueOrder) {
            Log::error('Epayco IPN monto no coincide', ['order_id' => $numOrder, 'gateway' => $x_amount, 'order' => $valueOrder]);
            return response()->json([
                'alert' => 'error',
                'message' => 'Monto no coincide'
            ], 400);
        }

        Log::info('Epayco IPN signature valid', ['order_id' => $numOrder]);

        $statusMap = [
            1 => ["alert" => "success", "message" => "Transacción aceptada", "status" => "processing"],
            2 => ["alert" => "error", "message" => "Transacción rechazada", "status" => "canceled"],
            3 => ["alert" => "warning", "message" => "Transacción pendiente", "status" => "pending_payment"],
            4 => ["alert" => "error", "message" => "Transacción fallida", "status" => "canceled"],
        ];

        $response = $statusMap[$x_cod_response] ?? [
            "alert" => "error",
            "message" => "Error en código de respuesta",
            "status" => "pending_payment"
        ];

        // Actualiza estado y transacción
        $this->orderRepository->update([
            'status' => $response['status'],
            'transaction_id' => $x_ref_payco,
        ], $this->order->id);

        // Si aprobado y puede facturar, crear invoice
        if ($response['status'] === 'processing' && $this->order->canInvoice()) {
            $this->invoiceRepository->create($this->prepareInvoiceData(), null, $response['status']);
        }

        // Desactiva carrito si lo teníamos activo
        if (session()->has('cart')) {
            Cart::deActivateCart();
        }

        return response()->json($response);
    }//-- end processOrder

    /**
     * Crea orden desde un carrito dado su ID.
     */
    protected function createOrderFromCartId(?int $cartId)
    {
        if (! $cartId) {
            return null;
        }

        Cart::activateCart($cartId);
        $cart = Cart::getCart();

        if (! $cart) {
            return null;
        }

        $data = (new OrderResource($cart))->jsonSerialize();

        $order = $this->orderRepository->create($data);

        Cart::deActivateCart();

        return $order;
    }

    /**
     * Prepares order's invoice data for creation.
     *
     * @return array
     */
    protected function prepareInvoiceData()
    {
        $invoiceData = ['order_id' => $this->order->id];

        foreach ($this->order->items as $item) {
            $invoiceData['invoice']['items'][$item->id] = $item->qty_to_invoice;
        }

        return $invoiceData;
    }
}
