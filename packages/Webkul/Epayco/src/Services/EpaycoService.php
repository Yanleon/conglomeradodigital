<?php

namespace Webkul\Epayco\Services;

use Illuminate\Support\Facades\Log;

class EpaycoService
{
    protected $testMode;

    public function __construct()
    {
        $this->testMode = core()->getConfigData('sales.payment_methods.epayco.testing_mode');
    }

    public function buildPayload()
    {
        $cart = app('Webkul\\Checkout\\Facades\\Cart')::getCart();
        $billing = $cart->billing_address;

        if (!$billing) {
            throw new \InvalidArgumentException('Billing address required');
        }

        $url_response = core()->getConfigData('sales.payment_methods.epayco.url_response');
        $url_confirmation = core()->getConfigData('sales.payment_methods.epayco.url_confirmation');
        $name_store = core()->getConfigData('sales.payment_methods.epayco.name_store');

        $address = is_array($billing->address)
            ? implode(' ', $billing->address)
            : $billing->address;

        $documentNumber = preg_replace('/\\D/', '', (string) ($billing->vat_id ?? '123456789'));

        return [
            'name' => $name_store,
            'description' => 'Compra en tienda',
            'invoice' => uniqid('order_'),
            'currency' => 'COP',
            'amount' => $cart->grand_total,
            'tax_base' => '0',
            'tax' => '0',
            'country' => 'co',
            'lang' => 'es',
            'external' => false,
            'test' => $this->testMode,
            'response' => $url_response,
            'confirmation' => $url_confirmation,
            'name_billing' => trim($cart->customer_first_name . ' ' . $cart->customer_last_name),
            'email_billing' => $cart->customer_email,
            'mobilephone_billing' => preg_replace('/\\D/', '', $billing->phone),
            'address_billing' => $address,
            'type_doc_billing' => 'CC',
            'number_doc_billing' => $documentNumber
        ];
    }

    public function verifyCharge($refPayco)
    {
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get("https://secure.epayco.co/validation/v1/reference/{$refPayco}");
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error('Epayco API error: ' . $e->getMessage());
            return null;
        }
    }
}