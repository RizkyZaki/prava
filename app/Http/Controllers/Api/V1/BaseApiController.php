<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\Api\ApiResponseTrait;
use Illuminate\Http\Request;

abstract class BaseApiController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get the currently authenticated user.
     */
    protected function currentUser(Request $request)
    {
        return $request->user();
    }
}
