<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

if (! function_exists('respondWithFail')) {
    function respondWithFail($error, int $code = 500): JsonResponse
    {
        return Response::json([
            'success' => false,
            'message' => is_array($error) ? ($error[1] ?? 'Unknown error') : $error,
        ], $code);
    }
}

if (! function_exists('responseWithSuccess')) {
    function responseWithSuccess($data = [], int $code = 200): JsonResponse
    {
        return Response::json([
            'success' => true,
            'data' => $data,
        ], $code);
    }
}
if (! defined('DEFAULT_HEADER_MESSAGE')) {
    define('DEFAULT_HEADER_MESSAGE', 'Welcome to Secomapp special offer!');
    define('DEFAULT_SUCCESS_MESSAGE', 'Your offer was created! Please install app to activate the offer!');
    define('DEFAULT_EXTEND_MESSAGE', 'Just install app then offer will be applied automatically!');
    define('DEFAULT_USED_MESSAGE', 'You have already claimed this offer!');
    define('DEFAULT_FAIL_MESSAGE', "Offer can't be created because of the following reasons:");

    define('DEFAULT_EXPIRED_REASON', 'This offer was expired!');
    define('DEFAULT_LIMIT_REASON', 'Offers have reached the limit!');
    define('DEFAULT_CONDITION_REASON', "Your store doesn't match app conditions!");
}
