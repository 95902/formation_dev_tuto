<?php

namespace Tests\Unit\Formatters;

use App\Services\Answering\Formatters\CliFormatter;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Formatters\Concerns\HasSampleAnswers;

class CliFormatterTest extends TestCase
{
    use HasSampleAnswers;

    public function test_it_formats_an_answer_with_several_citations(): void
    {
        $expected = "Question : Comment declarer un sinistre auto ?\n\n"
            ."Il faut contacter votre assureur sous 5 jours ouvres.\n\n"
            ."2 sources :\n"
            ."  - wiki/sinistre-auto.md:12  Le sinistre doit etre declare par ecrit ou envoye via app...\n"
            ."  - wiki/delais.md:3  Reponse breve\n"
            ."\n[contexte ~1234 tokens]";

        $this->assertSame($expected, (new CliFormatter)->format($this->answerWithSeveralCitations()));
    }

    public function test_it_uses_the_singular_when_there_is_one_citation(): void
    {
        $expected = "Question : Comment declarer un sinistre auto ?\n\n"
            ."Il faut contacter votre assureur sous 5 jours ouvres.\n\n"
            ."1 source :\n"
            ."  - wiki/delais.md:3  Reponse breve\n"
            ."\n[contexte ~42 tokens]";

        $this->assertSame($expected, (new CliFormatter)->format($this->answerWithOneCitation()));
    }

    public function test_it_reports_when_there_are_no_citations(): void
    {
        $expected = "Question : Comment declarer un sinistre auto ?\n\n"
            ."Il faut contacter votre assureur sous 5 jours ouvres.\n\n"
            ."Aucune source.\n"
            ."\n[contexte ~10 tokens]";

        $this->assertSame($expected, (new CliFormatter)->format($this->answerWithNoCitations()));
    }
}
