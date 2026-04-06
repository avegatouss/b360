<?php

declare(strict_types=1);

namespace Modules\Core\Contracts;

use Modules\Core\DTOs\ChannelCreditDTO;
use Modules\Core\DTOs\ChannelDTO;
use Modules\Core\DTOs\ChannelMarginDTO;
use Modules\Core\DTOs\ChannelPriceDTO;
use Modules\Core\DTOs\OrderQuantityLimitsDTO;

interface ChannelRepositoryInterface
{
    public function findById(int $channelId): ?ChannelDTO;

    public function getChannelPrice(int $channelId, int $productId): ?ChannelPriceDTO;

    public function getChannelMargin(int $channelId, int $productId): ?ChannelMarginDTO;

    public function getActiveCredit(int $channelId): ?ChannelCreditDTO;

    public function getOrderQuantityLimits(int $channelId, int $productId): OrderQuantityLimitsDTO;
}
