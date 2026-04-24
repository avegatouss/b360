<?php

namespace Modules\Eshop360\Domain\Finance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Database\Traits\BelongsToInstance;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class AccountTransfer extends Model
{
    use BelongsToChannel, BelongsToInstance, HasFactory;

    protected $morphClass = \Modules\Eshop360\Models\AccountTransfer::class;

    protected $table = 'eshop_account_transfers';

    protected $fillable = [
        'channel_id',
        'instance_id',
        'from_account_id',
        'to_account_id',
        'amount',
        'fee',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
    ];

    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
