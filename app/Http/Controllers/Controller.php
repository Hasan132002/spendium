<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Traits\AuthorizationChecker;
use App\Traits\HasActionLogTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizationChecker, AuthorizesRequests, DispatchesJobs, HasActionLogTrait, ValidatesRequests;
    protected function success($message, $data = [], $code = 200)
    {
        return response()->json([
            'Code' => $code,
            'Message' => $message,
            'Data' => $data
        ], $code);
    }

    protected function error($message, $data = null, $code = 400)
    {
        return response()->json([
            'Code' => $code,
            'Message' => $message,
            'Data' => $data
        ], $code);
    }
}
