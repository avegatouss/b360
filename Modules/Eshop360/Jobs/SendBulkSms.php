<?php

namespace Modules\Eshop360\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Modules\Eshop360\Models\BulkMessageLog;
use Modules\Eshop360\Services\SmsService;

class SendBulkSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 600;

    public function __construct(
        public BulkMessageLog $log,
        public Collection $recipients,
    ) {}

    public function handle(SmsService $smsService): void
    {
        if ($this->log->status === 'pending') {
            $this->log->update([
                'status' => 'processing',
                'started_at' => now(),
            ]);
        }

        $chunks = $this->recipients->chunk(50);

        foreach ($chunks as $chunk) {
            foreach ($chunk as $customer) {
                if (!$customer->phone) {
                    $this->log->increment('failed_count');
                    continue;
                }

                try {
                    $success = $smsService->send($customer->phone, $this->log->message);

                    if ($success) {
                        $this->log->increment('sent_count');
                    } else {
                        $this->log->increment('failed_count');
                    }
                } catch (\Throwable $e) {
                    Log::error('Bulk SMS failed for customer', [
                        'customer_id' => $customer->id,
                        'error' => $e->getMessage(),
                    ]);
                    $this->log->increment('failed_count');
                }
            }
        }

        // Check if all chunks have been processed
        $this->log->refresh();
        $processed = $this->log->sent_count + $this->log->failed_count;
        if ($processed >= $this->log->total_recipients) {
            $this->log->update([
                'status' => $this->log->failed_count === $this->log->total_recipients ? 'failed' : 'completed',
                'completed_at' => now(),
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendBulkSms job failed', [
            'log_id' => $this->log->id,
            'error' => $exception->getMessage(),
        ]);

        $this->log->update([
            'status' => 'failed',
            'completed_at' => now(),
        ]);
    }
}
