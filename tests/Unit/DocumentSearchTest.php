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
}
