<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PersonnePhysique extends Model  implements HasMedia
{
    use SoftDeletes, HasFactory, InteractsWithMedia;
    protected $fillable = [
        'personne_id',
        'civilite',
        'nom',
        'prenom',
        'nom_affichage',
        'nom_complet',
        'date_naissance',
        'lieu_naissance',
        'profession',
        'type_piece',
        'numero_piece',
        'lieu_delivrance',
        'date_delivrance',
        'date_expiration',
        'photo'
    ];


    protected $casts = [
        'date_naissance' => 'date',
        'date_delivrance' => 'date',
        'date_expiration' => 'date',
    ];

    public function personne()
    {
        return $this->belongsTo(Personne::class);
    }

    public function representations()
    {
        return $this->hasMany(Representant::class, 'representant_id');
    }

    public function getNomAffichageAttribute()
    {
         return trim(($this->civilite ? $this->civilite.' ' : '').$this->nom.' '.$this->prenom);
    }

    public function getNomCompletAttribute()
    {
        return trim("{$this->nom} {$this->prenom}");
    }

    // Représentations principales
    public function representationsPrincipales()
    {
        return $this->representations()->where('est_principal', true);
    }

    // Âge de la personne
    public function getAgeAttribute()
    {
        return $this->date_naissance ? $this->date_naissance->age : null;
    }

    public function user()
    {
        return $this->hasOne(User::class, 'personne_physique_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos')->singleFile();
        $this->addMediaCollection('documents_piece_identite');
        $this->addMediaCollection('documents_autre');
    }
}
