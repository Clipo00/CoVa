<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Marketplace Feature Flag
    |--------------------------------------------------------------------------
    |
    | Controls whether marketplace features (publish, voting, etc.) are
    | enabled. Set to true in .env only when marketplace infrastructure
    | is ready.
    |
    */
    'enabled' => env('MARKETPLACE_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Billing Feature Flag
    |--------------------------------------------------------------------------
    |
    | Controls whether billing/plan-based checks are enforced for marketplace
    | operations. When disabled, plan gates are skipped.
    |
    */
    'billing_enabled' => env('BILLING_ENABLED', false),
];
