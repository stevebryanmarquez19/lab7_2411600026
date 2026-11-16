<?php

return [
    'currency' => env('PROCUREMENT_CURRENCY', '₱'),

    // VAT applied to purchase order subtotals (0.12 = 12%).
    'tax_rate' => (float) env('PROCUREMENT_TAX_RATE', 0.12),

    // Orders at or above this total need an administrator (managers cannot approve them).
    'admin_approval_threshold' => (float) env('PROCUREMENT_ADMIN_THRESHOLD', 50000),

    // Reorder point inputs: (average daily usage x lead time) + safety stock.
    'default_lead_time_days' => 7,
    'default_safety_stock' => 10,
    'usage_window_days' => 30,   // how many days of 'out' transactions are averaged
    'review_period_days' => 14,  // extra days of demand to order beyond the reorder point

    // Receives the scheduled-command output e-mail (leave empty to disable).
    'admin_email' => env('PROCUREMENT_ADMIN_EMAIL'),

    // Cache lifetimes in seconds.
    'cache_ttl' => 600,
    'widget_ttl' => 60,
];
