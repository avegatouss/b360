<?php

declare(strict_types=1);

namespace Modules\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChannelCreditUpdated
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  'allocated'|'used'|'cancelled'  $action
     */
    public function __construct(
        public readonly int $creditId,
        public readonly int $channelId,
        public readonly float $newRemainingAmount,
        public readonly string $action,
    ) {}
}
