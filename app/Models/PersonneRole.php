<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PersonneRole extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'personne_id',
        'reference_role',
        'role',
        'metadata',
    ];

    protected $casts = [
        'role' => 'string',
        'metadata' => 'array',
    ];

    public function personne()
    {
        return $this->belongsTo(Personne::class);
    }
}
