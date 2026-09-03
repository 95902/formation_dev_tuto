<?php

namespace Tests\Unit\Formatters;

use App\Services\Answering\Formatters\JsonFormatter;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Formatters\Concerns\HasSampleAnswers;

class JsonFormatterTest extends TestCase
{
    use HasSampleAnswers;

    public function test_to_array_shortens_excerpts_and_lists_every_source(): void
    {
        $expected = [
            'question' => 'Comment declarer un sinistre auto ?',
            'answer' => 'Il faut contacter votre assureur sous 5 jours ouvres.',
            'source_count' => 2,
            'sources' => [
                [
                    'label' => 'wiki/sinistre-auto.md:12',
                    'path' => 'wiki/sinistre-auto.md',
                    'line' => 12,
                    'excerpt' => "Le sinistre doit etre declare par ecrit ou envoye via app...",
                ],
                [
                    'label' => 'wiki/delais.md:3',
                    'path' => 'wiki/delais.md',
                    'line' => 3,
                    'excerpt' => 'Reponse breve',
                ],
            ],
            'context_tokens' => 1234,
        ];

        $this->assertSame($expected, (new JsonFormatter)->toArray($this->answerWithSeveralCitations()));
    }

    public function test_to_array_reports_an_empty_source_list(): void
    {
        $expected = [
            'question' => 'Comment declarer un sinistre auto ?',
            'answer' => 'Il faut contacter votre assureur sous 5 jours ouvres.',
            'source_count' => 0,
            'sources' => [],
            'context_tokens' => 10,
        ];

        $this->assertSame($expected, (new JsonFormatter)->toArray($this->answerWithNoCitations()));
    }

    public function test_format_pretty_prints_the_same_data_as_json(): void
    {
        $formatter = new JsonFormatter;
        $answer = $this->answerWithSeveralCitations();

        $this->assertSame(
            json_encode($formatter->toArray($answer), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $formatter->format($answer)
        );
    }
}
