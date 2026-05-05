<?php

namespace Modules\Eshop360\Domain\Communication\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class TicketMessage extends Model
{
    use BelongsToChannel, HasFactory;

    protected $table = 'eshop_ticket_messages';

    protected $fillable = [
        'channel_id',
        'ticket_id',
        'user_id',
        'message',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
