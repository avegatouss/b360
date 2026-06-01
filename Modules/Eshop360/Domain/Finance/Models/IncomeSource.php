<?php

namespace Modules\Eshop360\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Database\Traits\BelongsToInstance;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class IncomeSource extends Model
{
    use BelongsToChannel, BelongsToInstance, HasFactory;

    protected $table = 'eshop_income_sources';

    protected $fillable = [
        'channel_id',
        'instance_id',
        'name',
    ];

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class, 'source_id');
    }
}
