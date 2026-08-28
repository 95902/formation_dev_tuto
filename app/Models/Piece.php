<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Piece extends Model
{
    use HasFactory;

    public const TYPES = ['constat', 'facture', 'photo', 'rapport_expertise', 'proces_verbal'];

    public const LIBELLES = [
        'constat' => 'Constat amiable',
        'facture' => 'Facture de réparation',
        'photo' => 'Photographies du dommage',
        'rapport_expertise' => 'Rapport d\'expertise',
        'proces_verbal' => 'Procès-verbal de police',
    ];

    protected $table = 'pieces';

    protected $fillable = ['sinistre_id', 'libelle', 'type', 'recue_le'];

    protected function casts(): array
    {
        return ['recue_le' => 'date'];
    }

    public function sinistre(): BelongsTo
    {
        return $this->belongsTo(Sinistre::class);
    }
}
