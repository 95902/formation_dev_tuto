<?php

namespace Database\Factories;

use App\Models\Contrat;
use App\Models\Sinistre;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sinistre>
 */
class SinistreFactory extends Factory
{
    protected $model = Sinistre::class;

    public function definition(): array
    {
        $fake = fake('fr_FR');
        $survenu = $fake->dateTimeBetween('-18 months', '-1 week');
        $declare = (clone $survenu)->modify('+'.$fake->numberBetween(0, 9).' days');

        return [
            'contrat_id' => Contrat::factory(),
            'reference' => 'SIN-'.$fake->unique()->numberBetween(100000, 999999),
            'nature' => $fake->randomElement(array_keys(Sinistre::NATURES)),
            'survenu_le' => $survenu->format('Y-m-d'),
            'declare_le' => $declare->format('Y-m-d H:i:s'),
            'description' => $fake->sentence(12),
            'statut' => $fake->randomElement(array_keys(Sinistre::STATUTS)),
            'montant_estime_cents' => $fake->numberBetween(35000, 1800000),
            'montant_regle_cents' => null,
            'gestionnaire' => $fake->randomElement(['C. Meunier', 'A. Rossi', 'K. Diallo', 'L. Fabre']),
        ];
    }
}
