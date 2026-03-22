<?php

return [
    [
        'key' => 'sales.payment_methods.boldpayment',
        'name' => 'Bold Payment',
        'info' => 'Bold Payment Gateway',
        'sort' => 3,
        'active' => true,
        'fields' => [
            [
                'name' => 'title',
                'title' => 'Titulo',
                'type' => 'text',
                'default_value' => 'Bold Payment',
            ],
            [
                'name' => 'description',
                'title' => 'Descripcion',
                'type' => 'textarea',
                'default_value' => 'Modulo de integracion con Bold Payments que podras activar desde el panel de administracion (Configuracion -> Metodos de Pago -> Bold Payment).',
            ],
            [
                'name' => 'merchant_id',
                'title' => 'Merchant ID',
                'type' => 'text',
            ],
            [
                'name' => 'api_key',
                'title' => 'API Key',
                'type' => 'password',
            ],
            [
                'name' => 'secret_key',
                'title' => 'Llave Secreta',
                'type' => 'password',
            ],
            [
                'name' => 'sandbox',
                'title' => 'Modo Prueba',
                'type' => 'boolean',
                'default_value' => true,
            ],
            [
                'name' => 'button_style',
                'title' => 'Estilo del Boton',
                'type' => 'text',
                'default_value' => 'dark-M',
                'info' => 'Combinacion color/tamano: dark|light con S|M|L, ej. dark-M.',
            ],
            [
                'name' => 'active',
                'title' => 'Activo',
                'type' => 'boolean',
                'default_value' => false,
            ],
        ],
    ],
];
