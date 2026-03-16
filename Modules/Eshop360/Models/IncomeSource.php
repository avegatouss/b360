<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Database\Traits\BelongsToInstance;

class IncomeSource extends Model
{
    use HasFactory, BelongsToInstance;

    protected $table = 'eshop_income_sources';

    protected $fillable = [
        'instance_id',
        'name',
    ];

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class, 'source_id');
    }
}
