<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SelectInventoryService
{
    /**
     * @return array<string, mixed>
     */
    public function buildInventory(): array
    {
        $files = $this->discoverBladeFiles();
        $items = [];

        foreach ($files as $file) {
            foreach ($this->extractSelectsFromFile($file) as $item) {
                $items[] = $item;
            }
        }

        $hardcoded = array_values(array_filter($items, fn (array $item): bool => $item['hardcoded_options']));
        $anonymous = array_values(array_filter($items, fn (array $item): bool => $item['identifier_type'] === 'anonymous'));

        return [
            'generated_at' => now()->toIso8601String(),
            'totals' => [
                'files' => count($files),
                'selects' => count($items),
                'hardcoded_selects' => count($hardcoded),
                'dynamic_selects' => count($items) - count($hardcoded),
                'anonymous_selects' => count($anonymous),
            ],
            'selects' => $items,
        ];
    }

    /**
     * @param  array<string, mixed>  $inventory
     * @return array<string, string>
     */
    public function writeArtifacts(array $inventory): array
    {
        $jsonPath = base_path(config('ui-selects.inventory.json_path', 'docs/ui/selects-inventory.json'));
        $markdownPath = base_path(config('ui-selects.inventory.markdown_path', 'docs/ui/selects-inventory.md'));
        $stubPath = base_path(config('ui-selects.inventory.stub_path', 'docs/ui/selects-overrides.stub.php'));

        File::ensureDirectoryExists(dirname($jsonPath));
        File::put($jsonPath, json_encode($inventory, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        File::put($markdownPath, $this->buildMarkdownSummary($inventory));
        File::put($stubPath, $this->buildOverridesStub($inventory));

        return [
            'json' => $jsonPath,
            'markdown' => $markdownPath,
            'stub' => $stubPath,
        ];
    }

    /**
     * @return list<string>
     */
    protected function discoverBladeFiles(): array
    {
        $roots = [
            base_path('resources/views'),
            base_path('Modules'),
        ];

        $files = [];

        foreach ($roots as $root) {
            if (! File::exists($root)) {
                continue;
            }

            foreach (File::allFiles($root) as $file) {
                if ($file->getExtension() !== 'php' || ! Str::endsWith($file->getFilename(), '.blade.php')) {
                    continue;
                }

                $files[] = $file->getPathname();
            }
        }

        sort($files);

        return $files;
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function extractSelectsFromFile(string $file): array
    {
        $content = File::get($file);
        preg_match_all('/<select\b[^>]*>.*?<\/select>/is', $content, $matches, PREG_OFFSET_CAPTURE);

        $items = [];

        foreach ($matches[0] ?? [] as $index => $match) {
            [$selectHtml, $offset] = $match;
            $openingTag = '';

            if (! preg_match('/<select\b([^>]*)>/is', $selectHtml, $opening)) {
                continue;
            }

            $openingTag = $opening[1] ?? '';
            $attributes = $this->parseAttributes($openingTag);
            $line = substr_count(substr($content, 0, $offset), "\n") + 1;
            $fileKey = $this->normalizeSegment($this->relativePath($file));
            $identifierType = 'anonymous';

            $key = $attributes['data-select-key'] ?? null;
            if (filled($key)) {
                $identifierType = 'data-select-key';
                $key = (string) $key;
            } elseif (filled($attributes['id'] ?? null)) {
                $identifierType = 'id';
                $key = 'id.' . $this->normalizeSegment((string) $attributes['id']);
            } elseif (filled($attributes['name'] ?? null)) {
                $identifierType = 'name';
                $key = 'name.' . $this->normalizeSegment((string) $attributes['name']);
            } else {
                $key = 'anonymous.' . $fileKey . '.line.' . $line . '.select.' . ($index + 1);
            }

            $optionCount = preg_match_all('/<option\b[^>]*>(.*?)<\/option>/is', $selectHtml, $optionMatches);
            $optionSamples = [];

            foreach ($optionMatches[1] ?? [] as $sample) {
                $cleanSample = trim(html_entity_decode(strip_tags($sample), ENT_QUOTES | ENT_HTML5));
                if ($cleanSample === '') {
                    continue;
                }

                $optionSamples[] = Str::limit($cleanSample, 80, '...');

                if (count($optionSamples) === 5) {
                    break;
                }
            }

            $containsBladeLogic = Str::contains($selectHtml, ['@foreach', '@for', '@if', '@switch', '@php', '{{', '{!!']);
            $hardcodedOptions = $optionCount > 0 && ! $containsBladeLogic;

            $items[] = [
                'key' => $key,
                'identifier_type' => $identifierType,
                'file' => $this->relativePath($file),
                'line' => $line,
                'id' => $attributes['id'] ?? null,
                'name' => $attributes['name'] ?? null,
                'class' => $attributes['class'] ?? null,
                'multiple' => array_key_exists('multiple', $attributes),
                'required' => array_key_exists('required', $attributes),
                'disabled' => array_key_exists('disabled', $attributes),
                'hardcoded_options' => $hardcodedOptions,
                'dynamic_blade_markup' => $containsBladeLogic,
                'option_count' => $optionCount ?: 0,
                'option_samples' => $optionSamples,
                'config_target' => $this->buildConfigTarget($identifierType, $attributes, $key),
            ];
        }

        return $items;
    }

    /**
     * @return array<string, string>
     */
    protected function parseAttributes(string $attributeString): array
    {
        preg_match_all('/([:@\w-]+)(?:\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s>]+)))?/', $attributeString, $matches, PREG_SET_ORDER);

        $attributes = [];

        foreach ($matches as $match) {
            $name = $match[1] ?? null;
            if (! $name) {
                continue;
            }

            $value = $match[3] ?? $match[4] ?? $match[5] ?? true;
            $attributes[$name] = $value;
        }

        return $attributes;
    }

    protected function buildConfigTarget(string $identifierType, array $attributes, string $key): ?string
    {
        return match ($identifierType) {
            'data-select-key' => "overrides.keys.{$key}",
            'id' => 'overrides.ids.' . $this->normalizeSegment((string) ($attributes['id'] ?? '')),
            'name' => 'overrides.names.' . $this->normalizeSegment((string) ($attributes['name'] ?? '')),
            default => null,
        };
    }

    protected function buildMarkdownSummary(array $inventory): string
    {
        $hardcoded = array_values(array_filter($inventory['selects'], fn (array $item): bool => $item['hardcoded_options']));
        $anonymous = array_values(array_filter($inventory['selects'], fn (array $item): bool => $item['identifier_type'] === 'anonymous'));

        usort($hardcoded, fn (array $left, array $right): int => [$left['file'], $left['line']] <=> [$right['file'], $right['line']]);
        usort($anonymous, fn (array $left, array $right): int => [$left['file'], $left['line']] <=> [$right['file'], $right['line']]);

        $lines = [
            '# Inventaire Selects',
            '',
            'Genere le ' . ($inventory['generated_at'] ?? now()->toIso8601String()),
            '',
            '## Totaux',
            '',
            '- Fichiers scannes : ' . ($inventory['totals']['files'] ?? 0),
            '- Selects detectes : ' . ($inventory['totals']['selects'] ?? 0),
            '- Selects avec options en dur : ' . ($inventory['totals']['hardcoded_selects'] ?? 0),
            '- Selects dynamiques Blade : ' . ($inventory['totals']['dynamic_selects'] ?? 0),
            '- Selects anonymes a cle explicite conseillée : ' . ($inventory['totals']['anonymous_selects'] ?? 0),
            '',
            '## Top selects en dur',
            '',
        ];

        foreach (array_slice($hardcoded, 0, 50) as $item) {
            $lines[] = '- `' . $item['key'] . '` [' . $item['file'] . ':' . $item['line'] . ']'
                . ' options=' . $item['option_count']
                . ' cible=' . ($item['config_target'] ?? 'data-select-key requis');
        }

        if (count($hardcoded) === 0) {
            $lines[] = '- Aucun select avec options en dur detecte';
        }

        $lines[] = '';
        $lines[] = '## Selects anonymes';
        $lines[] = '';

        foreach (array_slice($anonymous, 0, 50) as $item) {
            $lines[] = '- `' . $item['key'] . '` [' . $item['file'] . ':' . $item['line'] . ']';
        }

        if (count($anonymous) === 0) {
            $lines[] = '- Aucun select anonyme detecte';
        }

        $lines[] = '';
        $lines[] = '## Usage';
        $lines[] = '';
        $lines[] = '- relancer `php artisan ui:inventory-selects --write` apres ajout ou modification de formulaires';
        $lines[] = '- parametrer les cas specifiques dans `config/ui-selects.php` via `overrides.keys`, `overrides.names` ou `overrides.ids`';
        $lines[] = '- ajouter `data-select-key` sur les selects anonymes qui doivent recevoir un parametrage stable';

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    protected function buildOverridesStub(array $inventory): string
    {
        $keyEntries = [];
        $nameEntries = [];
        $idEntries = [];

        foreach ($inventory['selects'] as $item) {
            if (! $item['hardcoded_options']) {
                continue;
            }

            if ($item['identifier_type'] === 'data-select-key') {
                $keyEntries[$item['key']] = $item['key'];
            }

            if ($item['identifier_type'] === 'name' && ! empty($item['name'])) {
                $normalizedName = $this->normalizeSegment((string) $item['name']);
                $nameEntries[$normalizedName] = $normalizedName;
            }

            if ($item['identifier_type'] === 'id' && ! empty($item['id'])) {
                $normalizedId = $this->normalizeSegment((string) $item['id']);
                $idEntries[$normalizedId] = $normalizedId;
            }
        }

        $lines = [
            '<?php',
            '',
            'return [',
            "    'overrides' => [",
            "        'keys' => [",
        ];

        foreach ($keyEntries as $keyEntry) {
            $lines[] = "            '{$keyEntry}' => [],";
        }

        $lines[] = "        ],";
        $lines[] = "        'names' => [";

        foreach ($nameEntries as $nameEntry) {
            $lines[] = "            '{$nameEntry}' => [],";
        }

        $lines[] = "        ],";
        $lines[] = "        'ids' => [";

        foreach ($idEntries as $idEntry) {
            $lines[] = "            '{$idEntry}' => [],";
        }

        $lines[] = "        ],";
        $lines[] = '    ],';
        $lines[] = '];';

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    protected function normalizeSegment(string $value): string
    {
        $value = str_replace(['[', ']'], ['.', ''], $value);
        $value = preg_replace('/[^a-zA-Z0-9]+/', '.', $value) ?? $value;

        return trim(Str::lower($value), '.');
    }

    protected function relativePath(string $absolutePath): string
    {
        return str_replace('\\', '/', Str::after($absolutePath, base_path() . DIRECTORY_SEPARATOR));
    }
}
