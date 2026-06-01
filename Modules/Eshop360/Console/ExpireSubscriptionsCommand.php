<?php

namespace Modules\Eshop360\Console;

use Illuminate\Console\Command;
use Modules\Billing\Services\SubscriptionManager;

class ExpireSubscriptionsCommand extends Command
{
    protected $signature = 'eshop360:expire-subscriptions';
    protected $description = 'Expire overdue trial and active subscriptions';

    public function handle(SubscriptionManager $manager): int
    {
        $count = $manager->expireOverdue();
        $this->info("Expired {$count} overdue subscription(s).");
        return self::SUCCESS;
    }
}
