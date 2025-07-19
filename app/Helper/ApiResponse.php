<?php
namespace App\Helpers;

class ApiResponse
{
    public static function success($message, $data = null, $code = 200)
    {
        return response()->json([
            'Code' => $code,
            'Message' => $message,
            'Data' => $data
        ], $code);
    }

    public static function error($message, $data = null, $code = 400)
    {
        return response()->json([
            'Code' => $code,
            'Message' => $message,
            'Data' => $data
        ], $code);
    }
}
