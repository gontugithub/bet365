<?php

namespace App\Traits;

trait TraitApiResponse
{
    
    public function successResponse($data, $message, $code){
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ],$code
        );
    }

     public function errorResponse($message, $code){
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
        ],$code
        );
    }
}