<?php

namespace Tests\Unit\Formatters\Concerns;

use App\Models\Document;
use App\Services\Answering\Answer;
use App\Services\Answering\Citation;

/**
 * Fixtures partagees entre les tests de caracterisation des formatters,
 * pour que les 5 suites decrivent exactement le meme Answer.
 */
trait HasSampleAnswers
{
    private function sampleQuestion(): string
    {
        return 'Comment declarer un sinistre auto ?';
    }

    private function sampleAnswerText(): string
    {
        return 'Il faut contacter votre assureur sous 5 jours ouvres.';
    }

    /**
     * @return array{0: Citation, 1: Citation}
     */
    private function sampleCitations(): array
    {
        $longExcerpt = "Le sinistre doit etre declare par ecrit ou envoye via application mobile dans un delai de cinq jours ouvres a compter du fait generateur.";

        $document1 = new Document(['title' => 'Sinistre auto', 'path' => 'wiki/sinistre-auto.md', 'content' => 'peu importe']);
        $document2 = new Document(['title' => 'Delais', 'path' => 'wiki/delais.md', 'content' => 'peu importe']);

        return [
            new Citation($document1, $longExcerpt, 12),
            new Citation($document2, 'Reponse breve', 3),
        ];
    }

    private function answerWithSeveralCitations(): Answer
    {
        return new Answer($this->sampleQuestion(), $this->sampleAnswerText(), $this->sampleCitations(), 1234);
    }

    private function answerWithOneCitation(): Answer
    {
        [, $second] = $this->sampleCitations();

        return new Answer($this->sampleQuestion(), $this->sampleAnswerText(), [$second], 42);
    }

    private function answerWithNoCitations(): Answer
    {
        return new Answer($this->sampleQuestion(), $this->sampleAnswerText(), [], 10);
    }
}
