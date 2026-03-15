<?php

namespace App\Exceptions;

use Exception;

class ErrorException extends Exception
{
    protected $data;

    public function __construct($message = "", $code = 0, $data = null)
    {
        parent::__construct($message, $code);
        $this->data = $data;
    }

    public function render($request)
    {
        $response = [
            'success' => false,
            'message' => $this->getMessage(),
            'data' => $this->data,
        ];

        return response()->json($response, $this->getCode());
    }
}
