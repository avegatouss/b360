<?php

$cinetpayConfig = [
    'base_url' => env('CINETPAY_BASE_URL', env('INETPAY_BASE_URL', 'https://api.inetpay.com/v1')),
    'merchant_id' => env('CINETPAY_MERCHANT_ID', env('INETPAY_MERCHANT_ID', '')),
    'secret_key' => env('CINETPAY_SECRET_KEY', env('INETPAY_SECRET_KEY', '')),
    'callback_url' => env('CINETPAY_CALLBACK_URL', env('INETPAY_CALLBACK_URL', '')),
    'supported_methods' => ['mobile_money', 'orange_money', 'mtn_money', 'card'],
];

return [
    'name' => 'Eshop360',

    // POS settings
    'pos' => [
        'default_layout' => 'pos-1',
        'printer_size' => 'A4',
        'sound_effects' => true,
        'payment_methods' => ['cash', 'card', 'cheque', 'bank_transfer', 'cinetpay'],
    ],

    // Invoice settings
    'invoice' => [
        'prefix' => 'INV-',
        'due_days' => 7,
        'round_off' => false,
        'show_company_details' => true,
    ],

    // Stock settings
    'stock' => [
        'low_stock_threshold' => 10,
        'track_expiry' => true,
        'allow_negative_stock' => false,
    ],

    // Order settings
    'order' => [
        'prefix' => 'ORD-',
        'auto_generate_invoice' => true,
    ],

    // Tax
    'tax' => [
        'default_rate' => 0,
        'inclusive' => false,
    ],

    // CinetPay payment gateway (legacy alias: inetpay)
    'cinetpay' => $cinetpayConfig,
    'inetpay' => $cinetpayConfig,

    // Feature tiers — which features require a paid subscription
    // 'free' = always available, 'paid' = requires active subscription with the feature
    'feature_tiers' => [
        // FREE — always available
        'pos.basic'           => 'free',
        'products.crud'       => 'free',
        'categories.crud'     => 'free',
        'brands.crud'         => 'free',
        'inventory.basic'     => 'free',
        'sales.basic'         => 'free',
        'invoices.basic'      => 'free',
        'customers.crud'      => 'free',
        'suppliers.crud'      => 'free',
        'purchases.basic'     => 'free',
        'reports.basic'       => 'free',
        'expenses.basic'      => 'free',
        'incomes.basic'       => 'free',
        'barcodes'            => 'free',
        'messages'            => 'free',
        'settings.basic'      => 'free',

        // PAID — requires active subscription plan with the feature key
        'channels'            => 'paid',
        'reports.advanced'    => 'paid',
        'reports.export'      => 'paid',
        'pdf.invoices'        => 'paid',
        'pdf.reports'         => 'paid',
        'payment.cinetpay'    => 'paid',
        'payment.inetpay'     => 'paid',
        'online_orders'       => 'paid',
        'installments'        => 'paid',
        'gift_cards'          => 'paid',
        'loans'               => 'paid',
        'hr'                  => 'paid',
        'charges'             => 'paid',
        'holdings'            => 'paid',
        'cash_registers'      => 'paid',
        'email_templates'     => 'paid',
        'sms'                 => 'paid',
        'support_tickets'     => 'paid',
        'promotions.advanced' => 'paid',
        'quotations'          => 'paid',
        'purchase_returns'    => 'paid',
        'stock_transfers'     => 'paid',
        'multi_warehouse'     => 'paid',
        'customer_groups'     => 'paid',
        'imports'             => 'paid',
        'audit_logs'          => 'paid',
        'scheduled_alerts'    => 'paid',
        'projects'            => 'paid',
    ],
];
