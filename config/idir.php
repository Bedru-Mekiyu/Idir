<?php

return [
    'default_locale' => 'am',
    'currency' => 'ETB',
    'currency_symbol' => 'ብር',

    /*
    |--------------------------------------------------------------------------
    | Sensible Placeholder Defaults
    |--------------------------------------------------------------------------
    | These are configurable per-idir. Values here are initial defaults only.
    | They are clearly flagged as placeholders, not researched facts.
    */
    'defaults' => [
        'dues_amount' => 200.00,          // Placeholder: 200 ETB/month
        'dues_frequency' => 'monthly',
        'late_fee_amount' => 50.00,        // Placeholder: 50 ETB late fee
        'late_fee_grace_days' => 7,        // Placeholder: 7-day grace period
        'vesting_period_days' => 90,       // Placeholder: 3 months before payout-eligible
        'required_approvals' => 2,         // Default: 2 committee approvals
    ],

    /*
    |--------------------------------------------------------------------------
    | Payout Trigger Presets
    |--------------------------------------------------------------------------
    | Pre-populated options when an idir is first created.
    | Idirs can add/remove/rename their own trigger types at any time.
    */
    'payout_trigger_presets' => [
        ['name' => 'death', 'label_am' => 'ሞት'],
        ['name' => 'wedding', 'label_am' => 'ሰርግ'],
        ['name' => 'emergency', 'label_am' => 'ድንገተኛ አደጋ'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Chapa Pending Payment Expiry
    |--------------------------------------------------------------------------
    | Hours after which a pending Chapa payment is marked as expired.
    | Applied by the idir:expire-pending-payments scheduled command.
    */
    'chapa_pending_expiry_hours' => 24,
];
