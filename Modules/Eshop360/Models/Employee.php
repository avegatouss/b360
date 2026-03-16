<?php

namespace Modules\Eshop360\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Database\Traits\BelongsToInstance;

class Employee extends Model
{
    use HasFactory, BelongsToInstance, SoftDeletes;

    protected $table = 'eshop_employees';

    protected $fillable = [
        'instance_id',
        'user_id',
        'name',
        'email',
        'phone',
        'position',
        'department',
        'salary',
        'commission_rate',
        'joined_at',
        'status',
    ];

    protected $casts = [
        'salary' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'joined_at' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function salaries(): HasMany
    {
        return $this->hasMany(EmployeeSalary::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(EmployeeCommission::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
