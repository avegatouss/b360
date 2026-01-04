<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Representant extends Model
{
    use SoftDeletes, HasFactory;
    protected $fillable = [
        'representant_id',
        'personne_morale_id',
        'est_principal',
        'fonction'
    ];

    protected $casts = [
        'est_principal' => 'bool',
    ];

    public function representant()
    {
        return $this->belongsTo(PersonnePhysique::class, 'representant_id');
    }

    public function personneMorale()
    {
       return $this->belongsTo(PersonneMorale::class, 'personne_morale_id');
    }
}
