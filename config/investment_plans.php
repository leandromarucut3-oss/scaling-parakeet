<?php

return [
    'premier' => [
        'name' => 'Premier',
        'min_amount_cents' => 15000,
        'max_amount_cents' => 79900,
        'daily_interest_bps' => 50,
        'duration_days' => 150,
    ],
    'deluxe' => [
        'name' => 'Deluxe',
        'min_amount_cents' => 80000,
        'max_amount_cents' => 799900,
        'daily_interest_bps' => 70,
        'duration_days' => 120,
    ],
    'presidential' => [
        'name' => 'Presidential',
        'min_amount_cents' => 800000,
        'max_amount_cents' => 100000000,
        'daily_interest_bps' => 90,
        'duration_days' => 90,
    ],
];
