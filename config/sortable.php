<?php

return [
    'sortable_fields' => [
        'users' => [
            'id', 'name', 'email', 'telegram', 'created_at', 'updated_at', 'status',
        ],
        'transactions' => [
            'id', 'first_name', 'amount', 'fee_amount', 'created_at', 'accepted_at', 'status',
        ],
        'withdrawals' => [
            'id', 'amount', 'fee_amount', 'created_at', 'accepted_at', 'status', 'first_name', 'order_id', 'user_id', 'site_id', 'paid_status',
        ],
        'wallets' => [
            'id',  'name', 'iban', 'total_amount', 'blocked_amount', 'last_sync_date', 'status', 'description', 'currency',
        ],
        'providers' => [
            'id', 'name', 'code', 'status',
        ],
        'banks' => [
            'id', 'name', 'priority', 'status', 'transaction_status', 'withdrawal_status', 'created_at', 'updated_at',
        ],
        'permissions' => [
            'id', 'name', 'guard_name', 'created_at', 'updated_at',
        ],
        'roles' => [
            'id', 'name', 'guard_name', 'status', 'created_at', 'updated_at',
        ],
        'activity-logs' => [
            'id', 'log_name', 'description', 'subject_type', 'subject_id', 'causer_id', 'created_at',
        ],
        'blacklist' => [
            'id', 'type', 'user_id', 'ip', 'reason', 'site_id', 'status', 'created_at', 'updated_at',
        ],
        'vendors' => [
            'id', 'name', 'email', 'status', 'deposit_amount', 'transaction_fee', 'withdrawal_fee', 'settlement_fee', 'created_at', 'updated_at',
        ],
        'vendor_users' => [
            'id', 'name', 'email', 'status', 'created_at', 'updated_at',
        ],
    ],
];
