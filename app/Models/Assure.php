<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Assure extends Model
{
    use HasFactory;

    protected $table = 'assures';

    protected $fillable = [
        'reference', 'civilite', 'nom', 'prenom', 'email',
        'telephone', 'date_naissance', 'adresse', 'code_postal', 'ville',
    ];

    protected function casts(): array
    {
        return ['date_naissance' => 'date'];
    }

    public function contrats(): HasMany
    {
        return $this->hasMany(Contrat::class);
    }

    public function sinistres(): HasManyThrough
    {
        return $this->hasManyThrough(Sinistre::class, Contrat::class);
    }

    public function nomComplet(): string
    {
        return "{$this->prenom} {$this->nom}";
    }
}
