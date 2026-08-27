<?php

namespace Tests\Feature;

use Database\Seeders\DocumentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AskEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DocumentSeeder::class);
    }

    public function test_it_rejects_an_empty_question(): void
    {
        $this->postJson('/api/ask', ['question' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors('question');
    }

    public function test_it_answers_with_sources(): void
    {
        $response = $this->postJson('/api/ask', ['question' => 'quelle est la fenetre de deploiement ?']);

        $response->assertOk()
            ->assertJsonStructure([
                'question',
                'answer',
                'source_count',
                'sources' => [['label', 'path', 'line', 'excerpt']],
                'context_tokens',
            ]);

        $this->assertGreaterThan(0, $response->json('source_count'));
    }

    public function test_it_caps_the_number_of_sources(): void
    {
        $response = $this->postJson('/api/ask', [
            'question' => 'documentation interne service',
            'limit' => 2,
        ]);

        $response->assertOk();
        $this->assertLessThanOrEqual(2, $response->json('source_count'));
    }
}
