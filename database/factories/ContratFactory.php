<?php

namespace Database\Factories;

use App\Models\Assure;
use App\Models\Contrat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contrat>
 */
class ContratFactory extends Factory
{
    protected $model = Contrat::class;

    public function definition(): array
    {
        $fake = fake('fr_FR');
        $effet = $fake->dateTimeBetween('-6 years', '-6 months');

        return [
            'assure_id' => Assure::factory(),
            'reference' => 'CTR-'.$fake->unique()->numberBetween(100000, 999999),
            'produit' => $fake->randomElement(Contrat::PRODUITS),
            'formule' => $fake->randomElement(Contrat::FORMULES),
            'date_effet' => $effet->format('Y-m-d'),
            'date_echeance' => (clone $effet)->modify('+1 year')->format('Y-m-d'),
            'statut' => $fake->randomElement(Contrat::STATUTS),
            'prime_annuelle_cents' => $fake->numberBetween(18000, 240000),
            'franchise_cents' => $fake->randomElement([10000, 15000, 20000, 30000, 50000]),
        ];
    }
}
