<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Statistics — Accepted Transaction Average
    |--------------------------------------------------------------------------
    |
    | When enabled, the admin statistics page shows the average approved
    | transaction amount (total approved amount / total approved count).
    |
    */
    'show_admin_accepted_average' => (bool) env('ADMIN_STATISTICS_SHOW_ACCEPTED_AVERAGE', false),
];
