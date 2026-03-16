<?php

namespace Modules\Lang\Services;

use Illuminate\Translation\FileLoader;

class DatabaseTranslationLoader extends FileLoader
{
    protected ?TranslationRepository $repository = null;

    protected function repository(): TranslationRepository
    {
        if (!$this->repository) {
            $this->repository = app(TranslationRepository::class);
        }
        return $this->repository;
    }

    /**
     * Load translations: merge file-based with DB overrides.
     */
    public function load($locale, $group, $namespace = null): array
    {
        // Load file-based translations first
        $fileTranslations = parent::load($locale, $group, $namespace);

        // Try to load DB translations
        try {
            $dbGroup = $namespace ? "{$namespace}::{$group}" : $group;
            $dbTranslations = $this->repository()->get($locale, $dbGroup);

            // DB translations override file-based ones
            return array_merge($fileTranslations, $dbTranslations);
        } catch (\Throwable $e) {
            // If DB is not available (install phase, etc.), return file translations
            return $fileTranslations;
        }
    }
}
