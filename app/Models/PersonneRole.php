<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PersonneRole extends Model
{
   use  HasFactory;
     protected $fillable = ['personne_id', 'role'];

    public function personne()
    {
        return $this->belongsTo(Personne::class);
    }
}
