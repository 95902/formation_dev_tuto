<?php

namespace Tests\Unit;

use App\Models\Contrat;
use App\Models\Garantie;
use App\Models\Sinistre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regles de calcul de l'indemnite : franchise opposable, plafond, reste a charge.
 */
class IndemniteTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_franchise_de_la_garantie_prime_sur_celle_du_contrat(): void
    {
        $sinistre = $this->sinistre(
            montantCents: 100000,
            franchiseContratCents: 30000,
            garantie: ['code' => 'DDE', 'franchise_cents' => 15000, 'plafond_cents' => 1000000],
        );

        $this->assertSame(15000, $sinistre->franchiseApplicableCents());
    }

    public function test_la_franchise_du_contrat_sert_de_repli_sans_garantie_mobilisable(): void
    {
        $sinistre = $this->sinistre(
            montantCents: 100000,
            franchiseContratCents: 30000,
            garantie: null,
        );

        $this->assertSame(30000, $sinistre->franchiseApplicableCents());
    }

    public function test_l_indemnite_est_plafonnee_par_la_garantie(): void
    {
        $sinistre = $this->sinistre(
            montantCents: 5000000,
            franchiseContratCents: 0,
            garantie: ['code' => 'DDE', 'franchise_cents' => 0, 'plafond_cents' => 1000000],
        );

        $this->assertSame(1000000, $sinistre->indemniteCents());
    }

    public function test_l_indemnite_ne_descend_jamais_sous_zero(): void
    {
        $sinistre = $this->sinistre(
            montantCents: 10000,
            franchiseContratCents: 0,
            garantie: ['code' => 'DDE', 'franchise_cents' => 50000, 'plafond_cents' => 1000000],
        );

        $this->assertSame(0, $sinistre->indemniteCents());
    }

    public function test_le_reste_a_charge_complete_l_indemnite(): void
    {
        $sinistre = $this->sinistre(
            montantCents: 100000,
            franchiseContratCents: 0,
            garantie: ['code' => 'DDE', 'franchise_cents' => 0, 'plafond_cents' => 1000000],
        );

        $this->assertSame(
            $sinistre->montant_estime_cents,
            $sinistre->indemniteCents() + $sinistre->resteAChargeCents(),
        );
    }

    public function test_le_delai_de_declaration_compte_les_jours_pleins(): void
    {
        $sinistre = $this->sinistre(100000, 0, null);
        $sinistre->survenu_le = '2026-03-02';
        $sinistre->declare_le = '2026-03-05 14:30:00';

        $this->assertSame(3, $sinistre->delaiDeclarationJours());
    }

    /**
     * @param  array<string, mixed>|null  $garantie
     */
    private function sinistre(int $montantCents, int $franchiseContratCents, ?array $garantie): Sinistre
    {
        $contrat = Contrat::factory()->create([
            'produit' => 'habitation',
            'franchise_cents' => $franchiseContratCents,
        ]);

        if ($garantie !== null) {
            Garantie::create([
                'contrat_id' => $contrat->id,
                'libelle' => Garantie::LIBELLES[$garantie['code']],
                'incluse' => true,
            ] + $garantie);
        }

        return Sinistre::factory()->create([
            'contrat_id' => $contrat->id,
            'nature' => 'degat_des_eaux',
            'montant_estime_cents' => $montantCents,
        ]);
    }
}
