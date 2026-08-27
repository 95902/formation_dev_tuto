<?php

namespace Tests\Unit;

use App\Models\Document;
use App\Services\Answering\Citation;
use PHPUnit\Framework\TestCase;

class CitationTest extends TestCase
{
    public function test_it_labels_a_citation_with_its_path_and_line(): void
    {
        $doc = new Document(['title' => 'Astreinte', 'content' => "un\ndeux", 'path' => 'wiki/astreinte.md']);

        $this->assertSame('wiki/astreinte.md:7', (new Citation($doc, 'peu importe', 7))->label());
    }

    public function test_it_falls_back_to_line_one_when_the_excerpt_is_absent(): void
    {
        $doc = new Document(['title' => 'Astreinte', 'content' => "un\ndeux", 'path' => 'wiki/astreinte.md']);

        $this->assertSame(1, Citation::locate($doc, 'absent du document')->line);
    }
}
