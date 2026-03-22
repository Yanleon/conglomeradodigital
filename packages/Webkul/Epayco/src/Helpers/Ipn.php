<?php

namespace Webkul\Epayco\Helpers;

use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderRepository;

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

            $this->public_key = core()->getConfigData('sales.payment_methods.epayco.public_key');
            $this->cust_id_client = core()->getConfigData('sales.payment_methods.epayco.cust_id_client');
            $this->p_key = core()->getConfigData('sales.payment_methods.epayco.p_key');
            $this->getOrder();
            return $this->processOrder();

        } catch (\Exception $e) {
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
        return $this->order = $this->orderRepository->findOneByField(['id' => $this->post['x_id_invoice'] ?? null]);
    }

    /**
     * Process order and create invoice.
     *
     * @return void
     */
    protected function processOrder()
    {
        $response = [];
        $x_ref_payco      = $this->post['x_ref_payco'];
        $x_transaction_id = $this->post['x_transaction_id'];
        $x_amount_raw     = $this->post['x_amount'];
        $x_amount         = $this->normalizeAmount($x_amount_raw);
        $x_currency_code  = $this->post['x_currency_code'];
        $x_signature      = $this->post['x_signature'];

        if (! $this->order) {
            return response()->json([
                'alert' => 'error',
                'message' => 'Orden no encontrada para la notificación de ePayco.',
                'status' => 'not_found',
            ], 404);
        }

        $signature = hash('sha256', $this->cust_id_client . '^' . $this->p_key . '^' . $x_ref_payco . '^' . $x_transaction_id . '^' . $x_amount_raw . '^' . $x_currency_code);

        $numOrder = $this->order->id;
        $valueOrder = $this->normalizeAmount($this->order->grand_total);

        $x_response     = $this->post['x_response'] == 'Aceptada' ? "paid" : $this->post['x_response'];
        $x_id_invoice   = (int)$this->post['x_id_invoice'];

        // se valida que el número de orden y el valor coincidan con los valores recibidos
        if ($x_id_invoice !== $numOrder || $x_amount !== $valueOrder) {
            return response()->json([
                "alert" => "error",
                "message" => "El valor o la orden no coinciden con lo reportado por ePayco.",
                "status" => "pending",
            ], 422);
        }

        if ($x_signature !== $signature) {
            return response()->json([
                "alert" => "error",
                "message" => "Firma inválida para la notificación de ePayco.",
                "status" => "pending",
            ], 422);
        }

        $x_cod_response = $this->post['x_cod_response'];

        $statusMap = [
            1 => ["alert" => "success", "message" => "Transacción aceptada", "status" => "paid"],
            2 => ["alert" => "error", "message" => "Transacción rechazada", "status" => "canceled"],
            3 => ["alert" => "warning", "message" => "Transacción pendiente", "status" => "pending_payment"],
            4 => ["alert" => "error", "message" => "Transacción fallida", "status" => "canceled"]
        ];

        $response = $statusMap[(int)$x_cod_response] ?? [
            "alert" => "error",
            "message" => "Error en código de respuesta",
            "status" => "pending"
        ];

        // Solo actualiza el estado si no es "paid"
        if ($this->order->status !== "paid") {
            $this->orderRepository->update(['status' => $response["status"]], $this->order->id);

            // Lógica adicional si el nuevo estado es "paid"
            if ($response["status"] === "paid" && $x_response === "paid" && $this->order->canInvoice()) {
                $this->invoiceRepository->create($this->prepareInvoiceData(), null, $response["status"]);
            }
        }

        return response()->json($response);
    }//-- end processOrder

    protected function normalizeAmount($amount): string
    {
        return number_format((float) $amount, 2, '.', '');
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
