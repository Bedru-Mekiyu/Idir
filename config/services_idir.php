<?php

/**
 * Idir-specific service configurations.
 * Merged into config/services.php by IdirServiceProvider.
 */
return [
    'chapa' => [
        'base_url' => env('CHAPA_BASE_URL', 'https://api.chapa.co/v1'),
        'secret_key' => env('CHAPA_SECRET_KEY'),
        'webhook_secret' => env('CHAPA_WEBHOOK_SECRET', 'test-chapa-webhook-secret'),
    ],

    'sms' => [
        // Options: afromessage | geezsms | africastalking
        // Change SMS_DRIVER in .env to switch provider without any code changes.
        'driver' => env('SMS_DRIVER', 'afromessage'),
    ],

    'afromessage' => [
        'base_url' => env('AFROMESSAGE_BASE_URL', 'https://api.afromessage.com/api'),
        'token' => env('AFROMESSAGE_TOKEN'),
        'sender_id' => env('AFROMESSAGE_SENDER_ID'),
        'identifier_id' => env('AFROMESSAGE_IDENTIFIER_ID'),
    ],

    'geezsms' => [
        'token' => env('GEEZSMS_TOKEN'),
        'sender_id' => env('GEEZSMS_SENDER_ID'),
        'shortcode_id' => env('GEEZSMS_SHORTCODE_ID'),
    ],

    'africastalking' => [
        'username' => env('AT_USERNAME', 'sandbox'),
        'api_key' => env('AT_API_KEY'),
        'sender_id' => env('AT_SENDER_ID'),
        'shortcode' => env('AT_SHORTCODE'),
    ],

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET'),
    ],

    'fayda' => [
        'base_url' => env('FAYDA_BASE_URL', 'https://esignet.ida.fayda.et'),
        'client_id' => env('FAYDA_CLIENT_ID'),
        'redirect_uri' => env('FAYDA_REDIRECT_URI'),
        'scopes' => env('FAYDA_SCOPES', 'openid profile phone'),
        'private_key' => env('FAYDA_PRIVATE_KEY_PATH'),
        'key_id' => env('FAYDA_KEY_ID', 'default-key-id'),
    ],

    'telebirr' => [
        'base_url' => env('TELEBIRR_BASE_URL', 'https://app.ethiomobilemoney.et:2121/ammapi/payment/service-openup'),
        'app_id' => env('TELEBIRR_APP_ID'),
        'app_key' => env('TELEBIRR_APP_KEY'),
        'short_code' => env('TELEBIRR_SHORT_CODE'),
        'public_key' => env('TELEBIRR_PUBLIC_KEY'), // Telebirr RSA public key for payload encryption
    ],

    'mpesa' => [
        'base_url' => env('MPESA_BASE_URL', 'https://sandbox.safaricom.et'),
        'consumer_key' => env('MPESA_CONSUMER_KEY'),
        'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
        'shortcode' => env('MPESA_SHORTCODE'),
        'passkey' => env('MPESA_PASSKEY'),
        'callback_url' => env('MPESA_CALLBACK_URL'),
    ],
];
