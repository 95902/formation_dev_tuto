<?php

namespace Database\Factories;

use App\Models\Piece;
use App\Models\Sinistre;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Piece>
 */
class PieceFactory extends Factory
{
    protected $model = Piece::class;

    public function definition(): array
    {
        $fake = fake('fr_FR');
        $type = $fake->randomElement(Piece::TYPES);

        return [
            'sinistre_id' => Sinistre::factory(),
            'libelle' => Piece::LIBELLES[$type] ?? $type,
            'type' => $type,
            'recue_le' => $fake->dateTimeBetween('-18 months', 'now')->format('Y-m-d'),
        ];
    }
}
