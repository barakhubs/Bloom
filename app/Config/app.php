<?php

$env = getenv('APP_ENV') ?: 'local';

// APP_DEBUG, if set, always wins. Otherwise default to debug-on everywhere except
// production - a deployment that forgets to set APP_ENV=production is the unsafe
// direction to default towards, so this fails safe rather than fails open.
$appDebugEnv = getenv('APP_DEBUG');
$debug = $appDebugEnv !== false ? filter_var($appDebugEnv, FILTER_VALIDATE_BOOLEAN) : $env !== 'production';

return [
    'env' => $env,
    'debug' => $debug,
    'base_url' => getenv('APP_URL') ?: 'http://localhost:8000',
];
