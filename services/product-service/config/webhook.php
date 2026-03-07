<?php

return [
    'timeout' => (int) env('WEBHOOK_TIMEOUT', 30),
    'secret'  => env('WEBHOOK_SECRET', ''),
];
