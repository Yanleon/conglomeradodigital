<?php

return [
    [
        'key'  => 'sales.payment_methods.bold',
        'name' => 'Bold',
        'info' => 'Configuración para el método de pago Bold',
        'sort' => 1,
        'fields' => [
            [
                'name'          => 'title',
                'title'         => 'Título',
                'type'          => 'text',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
                'default_value' => 'Bold',
            ],
            [
                'name'          => 'description',
                'title'         => 'Descripción',
                'type'          => 'textarea',
                'channel_based' => false,
                'locale_based'  => true,
                'default_value' => 'Paga fácilmente con Bold.',
            ],
            [
                'name'          => 'active',
                'title'         => 'Activo',
                'type'          => 'boolean',
                'default_value' => true,
            ],
            [
                'name'          => 'sort',
                'title'         => 'Orden',
                'type'          => 'number',
                'default_value' => 1,
            ],
        ],
    ],
];
