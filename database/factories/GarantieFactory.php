<?php

namespace Database\Factories;

use App\Models\Contrat;
use App\Models\Garantie;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Garantie>
 */
class GarantieFactory extends Factory
{
    protected $model = Garantie::class;

    public function definition(): array
    {
        $fake = fake('fr_FR');
        $code = $fake->randomElement(array_values(Garantie::CODE_PAR_NATURE));

        return [
            'contrat_id' => Contrat::factory(),
            'code' => $code,
            'libelle' => Garantie::LIBELLES[$code] ?? $code,
            'plafond_cents' => $fake->randomElement([500000, 1000000, 2500000, 5000000]),
            'franchise_cents' => $fake->randomElement([7500, 10000, 15000, 25000]),
            'incluse' => true,
        ];
    }
}
