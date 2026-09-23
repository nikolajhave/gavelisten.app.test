<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Legacy Data Path
    |--------------------------------------------------------------------------
    |
    | The path to the legacy JSON file containing exported family, user,
    | and wish data.
    |
    */
    'data_path' => resource_path('wishData.json'),

    /*
    |--------------------------------------------------------------------------
    | Legacy User Mappings
    |--------------------------------------------------------------------------
    |
    | Map legacy users by name (e.g. 'Nikolaj') or legacy ID (e.g. 8)
    | to their contact details (phone number, email address, name).
    |
    | Examples:
    |   'Nikolaj' => [
    |       'name' => 'Nikolaj',
    |       'phone' => '20231120',
    |       'email' => null,
    |       'legacy_id' => 8,
    |   ],
    |
    */
    'users' => [
        'Nikolaj' => [
            'name' => 'Nikolaj',
            'phone' => '20231120',
            'email' => null,
            'legacy_id' => 8,
        ],
        'Nilas' => [
            'name' => 'Nilas',
            'phone' => null,
            'email' => 'nilas.have@gmail.com',
            'legacy_id' => null,
        ],
    ],
];
