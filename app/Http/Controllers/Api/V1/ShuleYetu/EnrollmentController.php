<?php

namespace App\Http\Controllers\Api\V1\ShuleYetu;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class EnrollmentController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json(['status' => 'stub']);
    }
}