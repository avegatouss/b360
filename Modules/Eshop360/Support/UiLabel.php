<?php

namespace Modules\Eshop360\Support;

use Illuminate\Support\Str;

final class UiLabel
{
    public static function enum(?string $value, string $fallback = '—'): string
    {
        if ($value === null || trim($value) === '') {
            return $fallback;
        }

        $value = trim($value);
        $translated = __($value);

        if ($translated !== $value) {
            return $translated;
        }

        $humanized = Str::of($value)
            ->replace(['_', '-'], ' ')
            ->lower()
            ->headline()
            ->toString();

        $translatedHumanized = __($humanized);

        if ($translatedHumanized !== $humanized) {
            return $translatedHumanized;
        }

        $lowerHumanized = Str::lower($humanized);
        $translatedLowerHumanized = __($lowerHumanized);

        if ($translatedLowerHumanized !== $lowerHumanized) {
            return $translatedLowerHumanized;
        }

        return $humanized;
    }
}
