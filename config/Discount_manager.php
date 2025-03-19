<?php

return [

    'customerio' => [
        'siteId' => env('CUSTOMERIO_ID', 'siteId'),
        'apiKey' => env('CUSTOMERIO_KEY', 'apiKey'),
        'appKey' => env('CUSTOMERIO_APP_KEY', 'appKey'),
    ],
    'ip_server' => env('IP_SERVER', '::1'),
    'affiliate_partner_ip' => env('AFFILIATE_PARTNER_IP', 'affiliate_partner_ip'),
];
