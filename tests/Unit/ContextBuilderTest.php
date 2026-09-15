<?php

namespace Tests\Unit;

use App\Models\Document;
use App\Services\Retrieval\ContextBuilder;
use App\Services\Retrieval\ScoredDocument;
use PHPUnit\Framework\TestCase;

class ContextBuilderTest extends TestCase
{
    private ContextBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new ContextBuilder;
    }

    public function test_it_leaves_a_short_text_untouched(): void
    {
        $this->assertSame('court', $this->builder->truncate('court', 100));
    }

    public function test_it_prefixes_each_block_with_its_path(): void
    {
        $doc = new Document(['title' => 'Astreinte', 'content' => 'planning', 'path' => 'wiki/astreinte.md']);
        $context = $this->builder->build(collect([new ScoredDocument($doc, 1.0)]));

        $this->assertStringContainsString('--- wiki/astreinte.md ---', $context);
    }

    public function test_it_stops_adding_blocks_once_the_budget_is_spent(): void
    {
        $results = collect(array_map(
            fn (int $i) => new ScoredDocument(
                new Document(['title' => "d{$i}", 'content' => str_repeat('a', 400), 'path' => "d{$i}.md"]),
                1.0,
            ),
            range(1, 20),
        ));

        $context = $this->builder->build($results, budget: 500);

        $this->assertLessThanOrEqual(600, strlen($context));
    }

    public function test_it_never_cuts_a_multibyte_character_in_half(): void
    {
        // "cafés" (5 caracteres, 6 octets : le "é" occupe 2 octets en UTF-8). Un
        // budget de 4 caracteres coupe une chaine "naive" par octets pile au milieu
        // de ce "é" (byte 3 sur 4), reproduisant le symptome du ticket.
        $text = 'cafés';

        $result = $this->builder->truncate($text, 4);

        $this->assertTrue(
            mb_check_encoding($result, 'UTF-8'),
            "Le texte tronque n'est pas une chaine UTF-8 valide : ".bin2hex($result)
        );
        $this->assertSame('café...', $result);
        $this->assertStringNotContainsString("\u{FFFD}", $result);
    }
}
