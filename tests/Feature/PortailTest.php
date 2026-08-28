<?php

namespace Tests\Feature;

use App\Models\Assure;
use App\Models\Contrat;
use Database\Seeders\DocumentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortailTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_tableau_de_bord_repond(): void
    {
        Contrat::factory()->create();

        $this->get('/')->assertOk()->assertSee('Tableau de bord');
    }

    public function test_la_liste_des_assures_se_cherche_par_nom(): void
    {
        Assure::factory()->create(['nom' => 'Delatour', 'reference' => 'ASS-2026-901']);
        Assure::factory()->create(['nom' => 'Vasseur', 'reference' => 'ASS-2026-902']);

        $this->get('/assures?q=Delatour')
            ->assertOk()
            ->assertSee('ASS-2026-901')
            ->assertDontSee('ASS-2026-902');
    }

    public function test_la_console_compas_cite_ses_sources(): void
    {
        $this->seed(DocumentSeeder::class);

        $this->get('/compas?question='.urlencode('abattement de vétusté'))
            ->assertOk()
            ->assertSee('doc-bareme-vetuste.md', false);
    }

    public function test_la_console_compas_sans_question_n_affiche_pas_de_reponse(): void
    {
        $this->seed(DocumentSeeder::class);

        $this->get('/compas')->assertOk()->assertDontSee('Sources (');
    }
}
