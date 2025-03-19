<?php

return [
    'DEFAULT_PER_PAGE' => 5,

    'DEFAULT_HEADER_MESSAGE' => 'Welcome to Secomapp special offer!',
    'DEFAULT_SUCCESS_MESSAGE' => 'Your offer was created! Please install app to activate the offer!',
    'DEFAULT_EXTEND_MESSAGE' => 'Just install app then offer will be applied automatically!',
    'DEFAULT_USED_MESSAGE' => 'You have already claimed this offer!',
    'DEFAULT_FAIL_MESSAGE' => "Offer can't be created because of the following reasons:",

    'DEFAULT_EXPIRED_REASON' => 'This offer was expired!',
    'DEFAULT_LIMIT_REASON' => 'Offers have reached the limit!',
    'DEFAULT_CONDITION_REASON' => "Your store doesn't match app conditions!",

    'SPECIAL_DATABASE_NAMES' => ['affiliate', 'freegifts_new'],

    'FILTER_VALIDATE_IP' => 275,
    'coupon_prefix' => 'GENAUTO',
    'affiliate_coupon_prefix' => 'AF-',
    'default_per_page' => 5,
    'automatic_apps' => [
        'banner', 'cs', 'pl', 'customer_attribute', 'spin_to_win',
        'smart_image_optimizer', 'seo_booster', 'affiliate', 'loyalty',
        'freegifts', 'freegifts_new', 'reviews_importer',
    ],

    'app_display_names' => [
        'qv' => 'Quick View',
        'fg' => 'Free gift',
        'pp' => 'Promotion Popup',
        'sl' => 'Store Locator',
        'sp' => 'Store Pickup',
        'bn' => 'Banner Slider',
        'cs' => 'Currency Switcher',
        'pl' => 'Product Label',
        'ca' => 'Customer Attribute',
        'sw' => 'Spin To Win',
        'io' => 'Smart Image Optimizer',
    ],
    'connection_map' => [
        'up_promote' => env('DB_CONNECTION_APP_13'),
        'bon' => env('DB_CONNECTION_APP_15'),
        'deco' => env('DB_CONNECTION_APP_3'),
        'bogos' => env('DB_CONNECTION_APP_16'),
        'search_pie' => env('DB_CONNECTION_APP_12'),
    ],
];
