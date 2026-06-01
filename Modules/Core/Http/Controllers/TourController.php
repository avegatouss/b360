<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Services\TourService;

final class TourController extends Controller
{
    /**
     * Get steps for a specific tour, filtered by current user's role.
     */
    public function steps(Request $request, string $tourId): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['steps' => []], 401);
        }

        $role = $user->roles->first()?->name ?? 'user';
        $steps = TourService::getSteps($tourId, $role);
        $meta = TourService::getTourMeta($tourId);

        return response()->json([
            'tour_id' => $tourId,
            'title' => $meta['title'] ?? $tourId,
            'steps' => $steps,
        ]);
    }

    /**
     * Mark a tour as completed for the current user.
     */
    public function complete(Request $request, string $tourId): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Non authentifie'], 401);
        }

        TourService::markCompleted($user->id, $tourId);

        return response()->json(['success' => true, 'tour_id' => $tourId]);
    }

    /**
     * Reset all tours for the current user.
     */
    public function reset(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Non authentifie'], 401);
        }

        TourService::resetAll($user->id);

        return response()->json(['success' => true]);
    }

    /**
     * List available tours for the current user with completion status.
     */
    public function available(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['tours' => []], 401);
        }

        $role = $user->roles->first()?->name ?? 'user';
        $tours = TourService::getAvailableTours($user->id, $role);

        return response()->json(['tours' => $tours]);
    }
}
