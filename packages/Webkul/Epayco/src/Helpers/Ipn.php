<?php

namespace Webkul\Epayco\Helpers;

use Illuminate\Support\Facades\Log;
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
        $this->order = $this->orderRepository->findOneByField(['id' => $this->post['x_id_invoice']]);
        Log::info('Epayco IPN looking for order', ['x_id_invoice' => $this->post['x_id_invoice'], 'found' => $this->order ? $this->order->id : null]);
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
        $x_amount         = (int)$this->post['x_amount'];
        $x_currency_code  = $this->post['x_currency_code'];
        $x_signature      = $this->post['x_signature'];

        $signature = hash('sha256', $this->cust_id_client . '^' . $this->p_key . '^' . $x_ref_payco . '^' . $x_transaction_id . '^' . $x_amount . '^' . $x_currency_code);

        $numOrder = $this->order->id;
        $valueOrder = (int)round($this->order->grand_total);

        $x_response     = $this->post['x_response'] == 'Aceptada' ? "paid" : $this->post['x_response'];
        $x_id_invoice   = (int)$this->post['x_id_invoice'];

        // se valida que el número de orden y el valor coincidan con los valores recibidos
        if ($x_id_invoice === $numOrder && $x_amount === $valueOrder) {
            //Validamos la firma
            if ($x_signature == $signature) {
                // se valida que la orden no haya sido procesada anteriormente
                Log::info('Epayco IPN signature valid', ['order_id' => $numOrder]);

                    $x_cod_response = (int) $this->post['x_cod_response'];

                    $statusMap = [
                        1 => ["alert" => "success", "message" => "Transacción aceptada", "status" => "paid"],
                        2 => ["alert" => "error", "message" => "Transacción rechazada", "status" => "canceled"],
                        3 => ["alert" => "warning", "message" => "Transacción pendiente", "status" => "pending_payment"],
                        4 => ["alert" => "error", "message" => "Transacción fallida", "status" => "canceled"]
                    ];

                    $response = $statusMap[$x_cod_response] ?? [
                        "alert" => "error",
                        "message" => "Error en código de respuesta",
                        "status" => "pending"
                    ];

                    // Si no es aprobada, no confirmamos ni avanzamos el estado
                    if ($x_cod_response !== 1) {
                        Log::warning('Epayco IPN no aprobado, no se confirma orden', ['order_id' => $numOrder, 'cod' => $x_cod_response]);
                        return response()->json($response);
                    }

                    // Solo actualiza el estado si no es "paid"
                    if ($this->order->status !== "paid") {
                        $this->orderRepository->update(['status' => $response["status"]], $this->order->id);

                        // Lógica adicional si el nuevo estado es "paid"
                        if ($response["status"] === "paid" && $x_response === "paid" && $this->order->canInvoice()) {
                            $this->invoiceRepository->create($this->prepareInvoiceData(), null, $response["status"]);
                        }
                    }

                return response()->json($response);
            }// end signature iif
        }//-- end validation order
    }//-- end processOrder

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
