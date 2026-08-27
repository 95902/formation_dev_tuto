<?php

namespace App\Services\Answering;

use App\Services\Llm\LlmClient;
use App\Services\Retrieval\ContextBuilder;
use App\Services\Retrieval\DocumentSearch;
use App\Services\Retrieval\ScoredDocument;

/**
 * Chaine complete : question -> recherche -> contexte -> reponse citee.
 */
class Answerer
{
    private const SYSTEM = <<<'TXT'
        Tu reponds aux questions des equipes en t'appuyant uniquement sur la
        documentation interne fournie. Si le contexte ne contient pas la
        reponse, dis-le au lieu d'inventer.
        TXT;

    public function __construct(
        private readonly DocumentSearch $search,
        private readonly ContextBuilder $context,
        private readonly LlmClient $llm,
    ) {}

    public function ask(string $question, int $limit = 5): Answer
    {
        $results = $this->search->search($question, $limit);
        $context = $this->context->build($results);

        $text = $this->llm->complete(self::SYSTEM, $this->prompt($question, $context));

        return new Answer(
            question: $question,
            text: $text,
            citations: $this->citations($results, $question),
            contextTokens: $this->context->estimateTokens($context),
        );
    }

    private function prompt(string $question, string $context): string
    {
        return "CONTEXTE :\n{$context}\n\nQUESTION : {$question}";
    }

    /**
     * @param  \Illuminate\Support\Collection<int, ScoredDocument>  $results
     * @return array<int, Citation>
     */
    private function citations($results, string $question): array
    {
        $terms = $this->search->tokenize($question);

        return $results
            ->map(fn (ScoredDocument $r) => Citation::locate($r->document, $this->excerpt($r, $terms)))
            ->all();
    }

    /**
     * Premiere ligne du document qui contient l'un des termes cherches.
     *
     * @param  array<int, string>  $terms
     */
    private function excerpt(ScoredDocument $result, array $terms): string
    {
        foreach ($result->document->lines() as $line) {
            foreach ($terms as $term) {
                if (mb_stripos($line, $term) !== false) {
                    return trim($line);
                }
            }
        }

        return trim($result->document->lines()[0] ?? '');
    }
}
