<?php

return [
    'name' => env('BUSINESS_NAME', 'Ubiquiti UniFi Kenya'),
    'legal_name' => env('BUSINESS_LEGAL_NAME') ?: 'Ubiquiti Unifi Kenya',
    'phone' => env('BUSINESS_PHONE') ?: '+254729299439',
    'whatsapp' => env('BUSINESS_WHATSAPP', env('BUSINESS_PHONE')),
    'email' => env('BUSINESS_EMAIL') ?: 'info@ubiquityunifikenya.co.ke',
    'address' => env('BUSINESS_ADDRESS') ?: 'Kimathi Street, Lyrics House',
    'hours' => env('BUSINESS_HOURS') ?: '8 AM - 5 PM',
    'return_window_days' => 7,
    'delivery_coverage' => env('BUSINESS_DELIVERY_COVERAGE'),
    'payment_options' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('BUSINESS_PAYMENT_OPTIONS', ''))
    ))),
    'social_profiles' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('BUSINESS_SOCIAL_PROFILES', ''))
    ))),
];
