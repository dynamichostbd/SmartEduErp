<?php

namespace App\Traits;

use Symfony\Component\HttpFoundation\Response;

trait ApiResponseTrait
{
    public function sendResponse($data, int $code = Response::HTTP_OK, string $message = 'Fetched successfully')
    {
        $array = ['success' => true, 'message' => $message, 'data' => $data];

        return response()->json($array, $code);
    }

    public function sendError(string $message, int $code = Response::HTTP_INTERNAL_SERVER_ERROR, $data = [])
    {
        $array = ['success' => false, 'message' => $message, 'data' => $data];

        return response()->json($array, $code);
    }
}
