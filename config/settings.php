<?php

declare(strict_types=1);

return [
    // Business Identity
    'business_name'  => env('APP_BUSINESS_NAME', 'Rima Craft'),
    'business_phone' => env('APP_BUSINESS_PHONE', ''),

    // Contact Information
    'address'        => env('SETTINGS_ADDRESS', ''),
    'email'          => env('SETTINGS_EMAIL', ''),
    'instagram'      => env('SETTINGS_INSTAGRAM', ''),
    'gmaps_iframe'   => env('SETTINGS_GMAPS_IFRAME', ''),
    'business_hours' => env('SETTINGS_BUSINESS_HOURS', ''),
];
