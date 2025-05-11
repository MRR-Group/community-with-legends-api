<?php

declare(strict_types=1);

return [
    "client_id" => env('TWITCH_CLIENT_ID'),
    "client_secret" => env('TWITCH_CLIENT_SECRET'),
    "code_redirect_uri" => env('TWITCH_CODE_REDIRECT_URL'),
    "token_redirect_uri" => env('TWITCH_TOKEN_REDIRECT_URL'),
];
