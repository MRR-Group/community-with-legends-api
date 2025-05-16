<?php

declare(strict_types=1);

return [
    "client_id" => env("TWITCH_CLIENT_ID"),
    "client_secret" => env("TWITCH_CLIENT_SECRET"),
    "code_redirect_uri" => env("TWITCH_CODE_REDIRECT_URL"),
    "token_redirect_uri" => env("TWITCH_TOKEN_REDIRECT_URL"),
    "login_redirect_url_mobile" => env("TWITCH_LOGIN_REDIRECT_URL_MOBILE"),
    "login_redirect_url_web" => env("TWITCH_LOGIN_REDIRECT_URL_WEB"),
    "login_redirect_error_url_mobile" => env("TWITCH_LOGIN_ERROR_REDIRECT_URL_MOBILE"),
    "login_redirect_error_url_web" => env("TWITCH_LOGIN_ERROR_REDIRECT_URL_WEB"),
];
