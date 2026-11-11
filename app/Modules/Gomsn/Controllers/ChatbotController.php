<?php

namespace App\Modules\Gomsn\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Gomsn\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function __construct(
        protected ChatbotService $chatbotService,
    ) {}

    /**
     * Handle incoming question for Chatbot Asisten (§5.8).
     */
    public function ask(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ]);

        $result = $this->chatbotService->ask(
            user: $request->user(),
            message: $validated['message']
        );

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ]);
    }
}

