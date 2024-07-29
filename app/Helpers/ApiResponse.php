<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Send a JSON response.
     *
     * @param int $code HTTP status code
     * @param string $message Response message
     * @param mixed $data Response data (optional)
     * @return JsonResponse
     */
    public static function sendResponse(int $code = 200, string $message = null, $data = []): JsonResponse
    {
        $response =  [
            'status' => $code,
            'message' => $message,
            'data' => $data
        ];

        return response()->json($response, $code);
    }
}
