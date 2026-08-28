<?php

namespace Database\Factories;

use App\Models\Assure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assure>
 */
class AssureFactory extends Factory
{
    protected $model = Assure::class;

    public function definition(): array
    {
        $fake = fake('fr_FR');
        $prenom = $fake->firstName();
        $nom = $fake->lastName();

        return [
            'reference' => 'ASS-'.$fake->unique()->numberBetween(10000, 99999),
            'civilite' => $fake->randomElement(['M.', 'Mme']),
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $fake->unique()->safeEmail(),
            'telephone' => '0'.$fake->numberBetween(600000000, 699999999),
            'date_naissance' => $fake->dateTimeBetween('-70 years', '-20 years')->format('Y-m-d'),
            'adresse' => $fake->streetAddress(),
            'code_postal' => $fake->numerify('#####'),
            'ville' => $fake->city(),
        ];
    }
}
