<?php

namespace Tests\Unit\Formatters;

use App\Services\Answering\Formatters\HtmlFormatter;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Formatters\Concerns\HasSampleAnswers;

class HtmlFormatterTest extends TestCase
{
    use HasSampleAnswers;

    public function test_it_formats_an_answer_with_several_citations(): void
    {
        $expected = "<p><strong>Question :</strong> Comment declarer un sinistre auto ?</p>\n"
            ."<p>Il faut contacter votre assureur sous 5 jours ouvres.</p>\n"
            ."<p><strong>2 sources :</strong></p>\n"
            ."<ul>\n"
            ."<li><code>wiki/sinistre-auto.md:12</code> Le sinistre doit etre declare par ecrit ou envoye via app...</li>\n"
            ."<li><code>wiki/delais.md:3</code> Reponse breve</li>\n"
            ."</ul>\n"
            .'<p><em>[contexte ~1234 tokens]</em></p>'."\n";

        $this->assertSame($expected, (new HtmlFormatter)->format($this->answerWithSeveralCitations()));
    }

    public function test_it_uses_the_singular_when_there_is_one_citation(): void
    {
        $expected = "<p><strong>Question :</strong> Comment declarer un sinistre auto ?</p>\n"
            ."<p>Il faut contacter votre assureur sous 5 jours ouvres.</p>\n"
            ."<p><strong>1 source :</strong></p>\n"
            ."<ul>\n"
            ."<li><code>wiki/delais.md:3</code> Reponse breve</li>\n"
            ."</ul>\n"
            .'<p><em>[contexte ~42 tokens]</em></p>'."\n";

        $this->assertSame($expected, (new HtmlFormatter)->format($this->answerWithOneCitation()));
    }

    public function test_it_reports_when_there_are_no_citations(): void
    {
        $expected = "<p><strong>Question :</strong> Comment declarer un sinistre auto ?</p>\n"
            ."<p>Il faut contacter votre assureur sous 5 jours ouvres.</p>\n"
            ."<p>Aucune source.</p>\n"
            .'<p><em>[contexte ~10 tokens]</em></p>'."\n";

        $this->assertSame($expected, (new HtmlFormatter)->format($this->answerWithNoCitations()));
    }
}
