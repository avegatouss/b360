<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Database\Traits\BelongsToInstance;

class ExpenseCategory extends Model
{
    use HasFactory, BelongsToInstance;

    protected $table = 'eshop_expense_categories';

    protected $fillable = [
        'instance_id',
        'name',
    ];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'category_id');
    }
}
