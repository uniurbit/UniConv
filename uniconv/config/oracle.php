<?php

return [
    'oracle' => [
        'driver'         => 'oracle',
        'tns'            => env('DB_TNS', ''),       
        'database'       => env('DB_DATABASE', 'xe'),
        'service_name'   =>  env('DB_SERVICENAME', ''),
        'username'       => env('DB_USERNAME_ORACLE', ''),
        'password'       => env('DB_PASSWORD_ORACLE', ''),
        'prefix'         => env('DB_PREFIX', ''),
    ],
];
