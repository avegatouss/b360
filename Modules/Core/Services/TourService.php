<?php

namespace Modules\Core\Services;

use Modules\Core\Models\TourCompletion;

class TourService
{
    /**
     * Returns tour steps for a given tour ID and user role.
     */
    public static function getSteps(string $tourId, string $role): array
    {
        $tours = config('tours', []);

        if (!isset($tours[$tourId])) {
            return [];
        }

        $tour = $tours[$tourId];

        // Check role access
        $allowedRoles = $tour['roles'] ?? [];
        if (!empty($allowedRoles) && !in_array($role, $allowedRoles, true)) {
            return [];
        }

        return $tour['steps'] ?? [];
    }

    /**
     * Get tour metadata (title, description) without steps.
     */
    public static function getTourMeta(string $tourId): ?array
    {
        $tours = config('tours', []);

        if (!isset($tours[$tourId])) {
            return null;
        }

        $tour = $tours[$tourId];

        return [
            'id' => $tourId,
            'title' => $tour['title'] ?? $tourId,
            'description' => $tour['description'] ?? '',
            'roles' => $tour['roles'] ?? [],
            'step_count' => count($tour['steps'] ?? []),
        ];
    }

    /**
     * Mark a tour as completed for a user.
     */
    public static function markCompleted(int $userId, string $tourId): void
    {
        TourCompletion::updateOrCreate(
            ['user_id' => $userId, 'tour_id' => $tourId],
            ['completed_at' => now()]
        );
    }

    /**
     * Check if user has completed a tour.
     */
    public static function isCompleted(int $userId, string $tourId): bool
    {
        return TourCompletion::where('user_id', $userId)
            ->where('tour_id', $tourId)
            ->exists();
    }

    /**
     * Reset all tours for a user.
     */
    public static function resetAll(int $userId): void
    {
        TourCompletion::where('user_id', $userId)->delete();
    }

    /**
     * Get all available tours for a given role, with completion status.
     */
    public static function getAvailableTours(int $userId, string $role): array
    {
        $tours = config('tours', []);
        $completedIds = TourCompletion::where('user_id', $userId)
            ->pluck('tour_id')
            ->toArray();

        $available = [];

        foreach ($tours as $tourId => $tour) {
            $allowedRoles = $tour['roles'] ?? [];
            if (!empty($allowedRoles) && !in_array($role, $allowedRoles, true)) {
                continue;
            }

            $available[] = [
                'id' => $tourId,
                'title' => $tour['title'] ?? $tourId,
                'description' => $tour['description'] ?? '',
                'step_count' => count($tour['steps'] ?? []),
                'completed' => in_array($tourId, $completedIds, true),
            ];
        }

        return $available;
    }
}
