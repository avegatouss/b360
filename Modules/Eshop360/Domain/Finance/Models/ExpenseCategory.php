<?php

namespace Modules\Eshop360\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Database\Traits\BelongsToInstance;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class ExpenseCategory extends Model
{
    use BelongsToChannel, BelongsToInstance, HasFactory;

    protected $morphClass = \Modules\Eshop360\Models\ExpenseCategory::class;

    protected $table = 'eshop_expense_categories';

    protected $fillable = [
        'channel_id',
        'instance_id',
        'name',
    ];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'category_id');
    }
}
