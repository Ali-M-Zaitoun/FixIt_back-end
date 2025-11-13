<?php

namespace App\Traits;

trait ResponseTrait
{
    public function successResponse($data, $message = "Request was successful", $code = 200)
    {
        return response()->json([
            'status' => __('messages.success'),
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public function errorResponse($message = "An error occurred", $code = 400, $errors = [])
    {
        return response()->json([
            'status' => __('messages.error'),
            'message' => $message,
            'errors' => $errors
        ], $code);
    }
}
