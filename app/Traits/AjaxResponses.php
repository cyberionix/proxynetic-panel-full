<?php

namespace App\Traits;

trait AjaxResponses
{

    public function successResponse($message = null,$extraParams = [])
    {
        return response()->json(array_merge([
            'success' => true,
            'message' => $message,
        ],$extraParams));
    }

    public function errorResponse($message = 'Bir hata meydana geldi.',$extraParams = [])
    {
        return response()->json(array_merge([
            'success' => false,
            'message' => $message,
        ],$extraParams));
    }
}
