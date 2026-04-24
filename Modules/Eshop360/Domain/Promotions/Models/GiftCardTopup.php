<?php

namespace Modules\Eshop360\Domain\Promotions\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Eshop360\Database\Traits\BelongsToChannel;

class GiftCardTopup extends Model
{
    use BelongsToChannel, HasFactory;

    protected $table = 'eshop_gift_card_topups';

    protected $fillable = [
        'channel_id',
        'gift_card_id',
        'amount',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function giftCard(): BelongsTo
    {
        return $this->belongsTo(GiftCard::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
