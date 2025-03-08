<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

if (! function_exists('respondWithFail')) {
    function respondWithFail($error, int $code): JsonResponse
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
function formatDate($date)
{
    return \Carbon\Carbon::parse($date)->format('Y-m-d'); // Example
}
