<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class Helpers
{
    public static function errorResponse(string $error, string $message, int $status): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => $error,
            'message' => $message,
        ], $status);
    }
}
