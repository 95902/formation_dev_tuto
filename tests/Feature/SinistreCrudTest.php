<?php

namespace Tests\Feature;

use App\Models\Contrat;
use App\Models\Sinistre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SinistreCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_liste_affiche_les_dossiers(): void
    {
        $sinistre = Sinistre::factory()->create(['reference' => 'SIN-2026-09999']);

        $this->get('/sinistres')
            ->assertOk()
            ->assertSee('SIN-2026-09999')
            ->assertSee($sinistre->contrat->assure->nom);
    }

    public function test_la_liste_se_filtre_par_statut(): void
    {
        Sinistre::factory()->create(['reference' => 'SIN-2026-00001', 'statut' => 'clos']);
        Sinistre::factory()->create(['reference' => 'SIN-2026-00002', 'statut' => 'refuse']);

        $this->get('/sinistres?statut=clos')
            ->assertOk()
            ->assertSee('SIN-2026-00001')
            ->assertDontSee('SIN-2026-00002');
    }

    public function test_la_fiche_affiche_le_dossier(): void
    {
        $sinistre = Sinistre::factory()->create(['description' => 'Fuite au plafond de la salle de bain.']);

        $this->get("/sinistres/{$sinistre->id}")
            ->assertOk()
            ->assertSee('Fuite au plafond de la salle de bain.')
            ->assertSee($sinistre->reference);
    }

    public function test_on_ouvre_un_dossier(): void
    {
        $contrat = Contrat::factory()->create();

        $this->post('/sinistres', [
            'contrat_id' => $contrat->id,
            'nature' => 'degat_des_eaux',
            'survenu_le' => '2026-06-01',
            'declare_le' => '2026-06-03T09:30',
            'description' => 'Infiltration constatée dans la cuisine.',
            'statut' => 'declare',
            'montant_estime' => 1250.50,
        ])->assertRedirect();

        $this->assertDatabaseHas('sinistres', [
            'contrat_id' => $contrat->id,
            'nature' => 'degat_des_eaux',
            'montant_estime_cents' => 125050,
        ]);
    }

    public function test_un_dossier_sans_description_est_refuse(): void
    {
        $contrat = Contrat::factory()->create();

        $this->post('/sinistres', [
            'contrat_id' => $contrat->id,
            'nature' => 'vol',
            'survenu_le' => '2026-06-01',
            'declare_le' => '2026-06-03T09:30',
            'description' => '',
            'statut' => 'declare',
            'montant_estime' => 400,
        ])->assertSessionHasErrors('description');

        $this->assertDatabaseCount('sinistres', 0);
    }

    public function test_on_supprime_un_dossier(): void
    {
        $sinistre = Sinistre::factory()->create();

        $this->delete("/sinistres/{$sinistre->id}")->assertRedirect();

        $this->assertDatabaseCount('sinistres', 0);
    }
}
