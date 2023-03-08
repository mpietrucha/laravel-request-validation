<?php

return [
    'enabled' => env('REQUEST_VALIDATION_ENABLED', true),

    'header' => env('REQUEST_VALIDATION_FIELD', 'X-Validate-Only')
];
