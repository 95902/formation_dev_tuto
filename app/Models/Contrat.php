<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contrat extends Model
{
    use HasFactory;

    public const PRODUITS = ['auto', 'moto', 'habitation', 'rc_pro'];

    public const FORMULES = ['essentiel', 'confort', 'premium'];

    public const STATUTS = ['actif', 'suspendu', 'resilie'];

    protected $table = 'contrats';

    protected $fillable = [
        'assure_id', 'reference', 'produit', 'formule', 'date_effet',
        'date_echeance', 'statut', 'prime_annuelle_cents', 'franchise_cents',
    ];

    protected function casts(): array
    {
        return [
            'date_effet' => 'date',
            'date_echeance' => 'date',
            'prime_annuelle_cents' => 'integer',
            'franchise_cents' => 'integer',
        ];
    }

    public function assure(): BelongsTo
    {
        return $this->belongsTo(Assure::class);
    }

    public function garanties(): HasMany
    {
        return $this->hasMany(Garantie::class);
    }

    public function sinistres(): HasMany
    {
        return $this->hasMany(Sinistre::class);
    }

    public function scopeActifs(Builder $query): Builder
    {
        return $query->where('statut', 'actif');
    }

    /**
     * Garantie couvrant une nature de sinistre, si le contrat la porte.
     */
    public function garantiePour(string $nature): ?Garantie
    {
        $code = Garantie::CODE_PAR_NATURE[$nature] ?? null;

        if ($code === null) {
            return null;
        }

        return $this->garanties->firstWhere('code', $code);
    }
}
