<?php

namespace Modules\Core\Console\Traits;

use Modules\Core\Models\CronLog;

trait LogsCronExecution
{
    /**
     * Execute a callback and log the result to cron_logs.
     *
     * Usage in handle():
     *   return $this->executeWithLogging(function () {
     *       // ... command logic ...
     *       return 'optional output message';
     *   });
     */
    protected function executeWithLogging(callable $callback): int
    {
        $start = microtime(true);

        try {
            $result = $callback();

            CronLog::create([
                'command' => $this->signature,
                'status' => 'success',
                'output' => is_string($result) ? $result : null,
                'duration_ms' => (int) ((microtime(true) - $start) * 1000),
                'executed_at' => now(),
            ]);

            return 0;
        } catch (\Exception $e) {
            CronLog::create([
                'command' => $this->signature,
                'status' => 'failed',
                'output' => $e->getMessage(),
                'duration_ms' => (int) ((microtime(true) - $start) * 1000),
                'executed_at' => now(),
            ]);

            $this->error($e->getMessage());

            return 1;
        }
    }
}
