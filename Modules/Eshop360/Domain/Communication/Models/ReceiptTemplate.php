<?php

namespace Modules\Eshop360\Domain\Communication\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Database\Traits\BelongsToInstance;
use Modules\Eshop360\Database\Traits\BelongsToChannel;
use Modules\Eshop360\Models\Store;

class ReceiptTemplate extends Model
{
    use BelongsToChannel, BelongsToInstance, HasFactory;

    protected $table = 'eshop_receipt_templates';

    protected $fillable = [
        'channel_id',
        'instance_id',
        'store_id',
        'name',
        'header_text',
        'footer_text',
        'show_logo',
        'show_address',
        'show_phone',
        'paper_width',
        'font_size',
        'is_default',
    ];

    protected $casts = [
        'show_logo' => 'boolean',
        'show_address' => 'boolean',
        'show_phone' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the default template for the current instance/store.
     */
    public static function getDefault(?int $storeId = null): ?self
    {
        $query = static::where('is_default', true);

        if ($storeId) {
            $query->where(function ($q) use ($storeId) {
                $q->where('store_id', $storeId)->orWhereNull('store_id');
            })->orderByRaw('store_id IS NULL ASC');
        }

        return $query->first();
    }
}
