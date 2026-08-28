<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Garantie extends Model
{
    use HasFactory;

    /**
     * Correspondance entre la nature d'un sinistre et la garantie qui le couvre.
     */
    public const CODE_PAR_NATURE = [
        'degat_des_eaux' => 'DDE',
        'vol' => 'VOL',
        'incendie' => 'INC',
        'bris_de_glace' => 'BDG',
        'collision' => 'COL',
        'rc' => 'RC',
    ];

    /**
     * Libelle commercial de chaque garantie.
     */
    public const LIBELLES = [
        'DDE' => 'Dégât des eaux',
        'VOL' => 'Vol et vandalisme',
        'INC' => 'Incendie et évènements assimilés',
        'BDG' => 'Bris de glace',
        'COL' => 'Dommages collision',
        'RC' => 'Responsabilité civile',
    ];

    protected $table = 'garanties';

    protected $fillable = ['contrat_id', 'code', 'libelle', 'plafond_cents', 'franchise_cents', 'incluse'];

    protected function casts(): array
    {
        return [
            'incluse' => 'boolean',
            'plafond_cents' => 'integer',
            'franchise_cents' => 'integer',
        ];
    }

    public function contrat(): BelongsTo
    {
        return $this->belongsTo(Contrat::class);
    }
}
