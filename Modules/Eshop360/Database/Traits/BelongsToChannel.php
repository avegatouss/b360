<?php

namespace Modules\Eshop360\Database\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Eshop360\Database\Scopes\ChannelScope;
use Modules\Eshop360\Domain\Channel\Models\DistributionChannel;
use Modules\Eshop360\Services\ChannelAccessService;
use Modules\Eshop360\Support\CurrentChannel;

/**
 * Trait BelongsToChannel — isolation automatique par channel.
 *
 * Applique le ChannelScope global et auto-injecte channel_id à la création.
 * Miroir du pattern BelongsToInstance pour l'isolation instance.
 */
trait BelongsToChannel
{
    public static function bootBelongsToChannel(): void
    {
        static::addGlobalScope(new ChannelScope);

        static::creating(function (Model $model) {
            if (empty($model->channel_id)) {
                if (CurrentChannel::isScoped()) {
                    $model->channel_id = CurrentChannel::id();
                } elseif (auth()->check() && ! app(ChannelAccessService::class)->isHubAdmin(auth()->user())) {
                    throw new \RuntimeException(
                        'Impossible de créer un '.class_basename($model).' sans contexte channel. '
                        .'Définissez channel_id explicitement ou naviguez dans un channel.'
                    );
                }
            }
        });
    }

    public static function withoutChannelScope(): Builder
    {
        return static::withoutGlobalScope(ChannelScope::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(DistributionChannel::class, 'channel_id');
    }
}
