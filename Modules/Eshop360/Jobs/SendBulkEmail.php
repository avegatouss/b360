<?php

namespace Modules\Eshop360\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Modules\Eshop360\Domain\Communication\Models\BulkMessageLog;
use Modules\Eshop360\Services\EmailService;

class SendBulkEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 600;

    public function __construct(
        public BulkMessageLog $log,
        public Collection $recipients,
        public ?string $templateSlug = null,
        public array $data = [],
    ) {}

    public function handle(EmailService $emailService): void
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
                if (! $customer->email) {
                    $this->log->increment('failed_count');

                    continue;
                }

                try {
                    $success = false;

                    if ($this->templateSlug) {
                        $variables = array_merge($this->data, [
                            'name' => $customer->name,
                            'email' => $customer->email,
                        ]);
                        $success = $emailService->sendFromTemplate(
                            $this->templateSlug,
                            $customer->email,
                            $variables,
                            $this->log->subject,
                        );
                    } else {
                        $success = $emailService->send(
                            $customer->email,
                            $this->log->subject ?? 'Message',
                            $this->log->message,
                        );
                    }

                    if ($success) {
                        $this->log->increment('sent_count');
                    } else {
                        $this->log->increment('failed_count');
                    }
                } catch (\Throwable $e) {
                    Log::error('Bulk email failed for customer', [
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
        Log::error('SendBulkEmail job failed', [
            'log_id' => $this->log->id,
            'error' => $exception->getMessage(),
        ]);

        $this->log->update([
            'status' => 'failed',
            'completed_at' => now(),
        ]);
    }
}
