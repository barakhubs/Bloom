<?php

return [
    'env' => getenv('APP_ENV') ?: 'local',
    'debug' => true,
    'base_url' => getenv('APP_URL') ?: 'http://localhost:8000',
];
