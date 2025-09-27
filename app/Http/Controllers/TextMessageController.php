<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendTextMessageRequest;

class TextMessageController extends Controller
{
    public function send(SendTextMessageRequest $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Function not implemented',
        ]);
    }
}
