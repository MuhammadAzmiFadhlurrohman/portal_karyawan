<?php

namespace App\Http\Controllers;

use App\Services\FcmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FcmController extends Controller
{
    public function __construct(
        protected FcmService $fcmService
    ) {}

    /**
     * Store or refresh FCM device token for authenticated user.
     */
    public function registerToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'min:10'],
            'platform' => ['nullable', 'string', 'in:web,android,ios'],
            'device_name' => ['nullable', 'string', 'max:150'],
        ]);

        $tokenRecord = $this->fcmService->registerToken(
            user: $request->user(),
            token: $validated['token'],
            platform: $validated['platform'] ?? 'web',
            deviceName: $validated['device_name'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'FCM Token registered successfully.',
            'id' => $tokenRecord->id,
        ]);
    }

    /**
     * Remove FCM device token for authenticated user.
     */
    public function deleteToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
        ]);

        $deleted = $this->fcmService->removeToken($request->user(), $validated['token']);

        return response()->json([
            'success' => $deleted,
            'message' => $deleted ? 'Token removed.' : 'Token not found.',
        ]);
    }

    /**
     * Fetch user notification preferences.
     */
    public function getPreferences(Request $request): JsonResponse
    {
        $pref = $this->fcmService->getUserPreferences($request->user());

        return response()->json([
            'success' => true,
            'preferences' => [
                'notify_pengajuan' => (bool) $pref->notify_pengajuan,
                'notify_lembur' => (bool) $pref->notify_lembur,
                'notify_tiket' => (bool) $pref->notify_tiket,
            ],
        ]);
    }

    /**
     * Update user notification preferences.
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'notify_pengajuan' => ['nullable', 'boolean'],
            'notify_lembur' => ['nullable', 'boolean'],
            'notify_tiket' => ['nullable', 'boolean'],
        ]);

        $pref = $this->fcmService->updatePreferences($request->user(), $validated);

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated.',
            'preferences' => [
                'notify_pengajuan' => (bool) $pref->notify_pengajuan,
                'notify_lembur' => (bool) $pref->notify_lembur,
                'notify_tiket' => (bool) $pref->notify_tiket,
            ],
        ]);
    }
}
