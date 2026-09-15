<?php

namespace Tests\Unit;

use App\Models\Document;
use App\Services\Retrieval\DocumentSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentSearchTest extends TestCase
{
    use RefreshDatabase;

    private DocumentSearch $search;

    protected function setUp(): void
    {
        parent::setUp();
        $this->search = new DocumentSearch;
    }

    public function test_it_ignores_terms_shorter_than_three_characters(): void
    {
        $this->assertSame(['deploiement'], $this->search->tokenize('le deploiement'));
    }

    public function test_it_scores_a_title_match_higher_than_a_body_match(): void
    {
        $inTitle = new Document(['title' => 'astreinte', 'content' => 'rien']);
        $inBody = new Document(['title' => 'rien', 'content' => 'astreinte']);

        $this->assertGreaterThan(
            $this->search->score($inBody, ['astreinte']),
            $this->search->score($inTitle, ['astreinte']),
        );
    }

    public function test_it_returns_nothing_when_no_document_matches(): void
    {
        Document::create([
            'title' => 'astreinte',
            'source_type' => 'wiki',
            'path' => 'wiki/astreinte.md',
            'content' => 'planning des semaines',
        ]);

        $this->assertCount(0, $this->search->search('kubernetes'));
    }

    public function test_it_ignores_the_case_of_the_query(): void
    {
        $this->assertSame(['astreinte'], $this->search->tokenize('Astreinte'));
    }

    public function test_it_finds_the_same_results_regardless_of_case(): void
    {
        Document::create([
            'title' => 'astreinte',
            'source_type' => 'wiki',
            'path' => 'wiki/astreinte.md',
            'content' => 'planning des semaines',
        ]);

        $lowercase = $this->search->search('astreinte');
        $uppercase = $this->search->search('Astreinte');

        $this->assertCount(1, $lowercase);
        $this->assertCount(1, $uppercase);
        $this->assertSame($lowercase->first()->document->id, $uppercase->first()->document->id);
    }

    public function test_it_returns_both_documents_with_the_same_title_but_different_paths(): void
    {
        Document::create([
            'title' => 'README',
            'source_type' => 'wiki',
            'path' => 'facturation/README.md',
            'content' => 'lancer en local avec npm run dev',
        ]);

        Document::create([
            'title' => 'README',
            'source_type' => 'wiki',
            'path' => 'notifications/README.md',
            'content' => 'lancer en local avec docker compose up',
        ]);

        $results = $this->search->search('lancer en local');

        $this->assertCount(2, $results);
        $paths = $results->pluck('document.path')->sort()->values();
        $this->assertSame(['facturation/README.md', 'notifications/README.md'], $paths->all());
    }

    public function test_deduplication_preserves_different_paths_with_same_title(): void
    {
        // Crée 3 docs: 2 READMEs + 1 autre
        Document::create([
            'title' => 'README',
            'source_type' => 'wiki',
            'path' => 'facturation/README.md',
            'content' => 'lancer en local npm',
        ]);

        Document::create([
            'title' => 'README',
            'source_type' => 'wiki',
            'path' => 'notifications/README.md',
            'content' => 'lancer en local docker',
        ]);

        Document::create([
            'title' => 'guide',
            'source_type' => 'wiki',
            'path' => 'docs/guide.md',
            'content' => 'autre contenu',
        ]);

        $results = $this->search->search('lancer local');

        // Les deux READMEs doivent être là (même titre, chemins différents)
        $this->assertCount(2, $results);
        $paths = $results->pluck('document.path')->sort()->values();
        $this->assertSame(['facturation/README.md', 'notifications/README.md'], $paths->all());
    }

    public function test_respects_limit_while_keeping_duplicate_titles(): void
    {
        // Crée 4 docs avec même titre mais chemins différents
        for ($i = 1; $i <= 4; $i++) {
            Document::create([
                'title' => 'README',
                'source_type' => 'wiki',
                'path' => "service{$i}/README.md",
                'content' => 'lancer en local method',
            ]);
        }

        // Avec limit=3, on devrait avoir 3 résultats (pas une déduplication agressive)
        $results = $this->search->search('lancer', 3);

        $this->assertCount(3, $results);
        $paths = $results->pluck('document.path')->all();
        // Vérifie que ce sont 3 chemins différents (pas de doublons)
        $this->assertCount(3, array_unique($paths));
    }

    public function test_unique_with_closure_actually_keeps_different_paths(): void
    {
        $doc1 = Document::create([
            'title' => 'README',
            'source_type' => 'wiki',
            'path' => 'facturation/README.md',
            'content' => 'lancer en local',
        ]);

        $doc2 = Document::create([
            'title' => 'README',
            'source_type' => 'wiki',
            'path' => 'notifications/README.md',
            'content' => 'lancer en local',
        ]);

        // Crée manually une Collection de ScoredDocument
        $scored = collect([
            new \App\Services\Retrieval\ScoredDocument($doc1, 3.0),
            new \App\Services\Retrieval\ScoredDocument($doc2, 3.0),
        ]);

        // Teste le dedupe directement (via reflection puisque c'est private)
        $reflection = new \ReflectionClass($this->search);
        $method = $reflection->getMethod('dedupe');
        $method->setAccessible(true);

        $deduped = $method->invoke($this->search, $scored);

        // Les deux doivent être présents car chemins différents
        $this->assertCount(2, $deduped, 'dedupe() should preserve both docs with same title but different paths');
        $paths = $deduped->pluck('document.path')->all();
        $this->assertSame(['facturation/README.md', 'notifications/README.md'], $paths);
    }

    public function test_dedupe_removes_actual_duplicates_by_path(): void
    {
        $doc1 = Document::create([
            'title' => 'README',
            'source_type' => 'wiki',
            'path' => 'facturation/README.md',
            'content' => 'content 1',
        ]);

        // Crée une Collection avec le même document deux fois (simule un bug de duplication)
        $scored = collect([
            new \App\Services\Retrieval\ScoredDocument($doc1, 3.0),
            new \App\Services\Retrieval\ScoredDocument($doc1, 3.0), // Duplicate
        ]);

        $reflection = new \ReflectionClass($this->search);
        $method = $reflection->getMethod('dedupe');
        $method->setAccessible(true);

        $deduped = $method->invoke($this->search, $scored);

        // Doit avoir qu'un seul document (le duplicate doit être supprimé)
        $this->assertCount(1, $deduped, 'dedupe() should remove duplicate paths');
        $this->assertSame('facturation/README.md', $deduped->first()->document->path);
    }

    public function test_complex_scenario_multiple_readme_same_title_different_paths(): void
    {
        // Reproduit le scénario décrit : 5 services avec README (titre identique)
        for ($i = 1; $i <= 5; $i++) {
            Document::create([
                'title' => 'README',
                'source_type' => 'wiki',
                'path' => "service{$i}/README.md",
                'content' => "lancer en local avec la methode $i",
            ]);
        }

        // Quand on cherche "lancer en local", tous les 5 doivent matcher
        $results = $this->search->search('lancer en local', 10);

        // Vérification 1: Les 5 documents doivent être présents
        $this->assertCount(5, $results, 'All 5 READMEs should be returned (same title, different paths)');

        // Vérification 2: Les chemins doivent tous être uniques
        $paths = $results->pluck('document.path')->all();
        $uniquePaths = array_unique($paths);
        $this->assertCount(5, $uniquePaths, 'All paths should be unique (no duplicates)');

        // Vérification 3: Chaque chemin attendu est présent
        $expectedPaths = ['service1/README.md', 'service2/README.md', 'service3/README.md', 'service4/README.md', 'service5/README.md'];
        foreach ($expectedPaths as $expected) {
            $this->assertContains($expected, $paths, "Path $expected should be in results");
        }
    }

    public function test_regression_old_bug_dedupe_by_title_would_fail(): void
    {
        // Ce test échouerait avec l'ancienne implémentation (dedupe par titre au lieu de path)
        // Deux documents avec MÊME TITRE mais chemins différents
        $doc1 = Document::create([
            'title' => 'Configuration',
            'source_type' => 'wiki',
            'path' => 'api/Configuration.md',
            'content' => 'setup api configuration',
        ]);

        $doc2 = Document::create([
            'title' => 'Configuration',
            'source_type' => 'wiki',
            'path' => 'frontend/Configuration.md',
            'content' => 'setup frontend configuration',
        ]);

        $results = $this->search->search('configuration');

        // CRITICAL: Avec l'ancien bug (dedupe par titre), seul 1 serait retourné
        // Avec le fix (dedupe par path), les 2 doivent être retournés
        $this->assertCount(2, $results, 'Both documents with same title but different paths must be returned');
        $this->assertCount(2, array_unique($results->pluck('document.path')->all()));
    }
}
