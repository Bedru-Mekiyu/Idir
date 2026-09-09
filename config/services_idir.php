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

    'afromessage' => [
        'base_url' => env('AFROMESSAGE_BASE_URL', 'https://api.afromessage.com/api'),
        'token' => env('AFROMESSAGE_TOKEN'),
        'sender_id' => env('AFROMESSAGE_SENDER_ID'),
        'identifier_id' => env('AFROMESSAGE_IDENTIFIER_ID'),
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
];
