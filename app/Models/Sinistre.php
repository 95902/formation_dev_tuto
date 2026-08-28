<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sinistre extends Model
{
    use HasFactory;

    public const NATURES = [
        'degat_des_eaux' => 'Dégât des eaux',
        'vol' => 'Vol',
        'incendie' => 'Incendie',
        'bris_de_glace' => 'Bris de glace',
        'collision' => 'Collision',
        'rc' => 'Responsabilité civile',
    ];

    public const STATUTS = [
        'declare' => 'Déclaré',
        'en_cours' => 'En cours',
        'expertise' => 'Expertise',
        'clos' => 'Clos',
        'refuse' => 'Refusé',
    ];

    /**
     * Abattement de vetuste applique au montant estime avant deduction
     * de la franchise. Taux unique, voir le bareme dans le corpus.
     */
    public const TAUX_VETUSTE = 0.87;

    protected $table = 'sinistres';

    protected $fillable = [
        'contrat_id', 'reference', 'nature', 'survenu_le', 'declare_le',
        'description', 'statut', 'montant_estime_cents', 'montant_regle_cents',
        'gestionnaire',
    ];

    protected function casts(): array
    {
        return [
            'survenu_le' => 'date',
            'declare_le' => 'datetime',
            'montant_estime_cents' => 'integer',
            'montant_regle_cents' => 'integer',
        ];
    }

    public function contrat(): BelongsTo
    {
        return $this->belongsTo(Contrat::class);
    }

    public function pieces(): HasMany
    {
        return $this->hasMany(Piece::class);
    }

    public function scopeEnInstruction(Builder $query): Builder
    {
        return $query->whereIn('statut', ['declare', 'en_cours', 'expertise']);
    }

    public function scopeClos(Builder $query): Builder
    {
        return $query->where('statut', 'clos');
    }

    public function natureLibelle(): string
    {
        return self::NATURES[$this->nature] ?? $this->nature;
    }

    public function statutLibelle(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    /**
     * Delai ecoule entre la survenance et la declaration, en jours pleins.
     */
    public function delaiDeclarationJours(): int
    {
        return (int) $this->survenu_le->startOfDay()->diffInDays($this->declare_le->startOfDay());
    }

    /**
     * Franchise opposable : celle de la garantie mobilisee si le contrat
     * la porte, sinon la franchise generale du contrat.
     */
    public function franchiseApplicableCents(): int
    {
        return $this->contrat->garantiePour($this->nature)?->franchise_cents
            ?? $this->contrat->franchise_cents;
    }

    /**
     * Indemnite due : montant estime, abattu de la vetuste, moins la
     * franchise, plafonne par la garantie mobilisee.
     */
    public function indemniteCents(): int
    {
        $apresVetuste = $this->montant_estime_cents * self::TAUX_VETUSTE;

        $net = $apresVetuste - $this->franchiseApplicableCents();

        $plafond = $this->contrat->garantiePour($this->nature)?->plafond_cents;

        if ($plafond !== null && $net > $plafond) {
            $net = $plafond;
        }

        return (int) max(0, $net);
    }

    /**
     * Ce qui reste a la charge de l'assure.
     */
    public function resteAChargeCents(): int
    {
        return max(0, $this->montant_estime_cents - $this->indemniteCents());
    }
}
