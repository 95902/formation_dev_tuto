<?php

namespace App\Services\Answering;

/**
 * Reponse rendue a l'utilisateur, avec ses sources.
 *
 * @param  array<int, Citation>  $citations
 */
readonly class Answer
{
    public function __construct(
        public string $question,
        public string $text,
        public array $citations,
        public int $contextTokens,
    ) {}

    public function hasSources(): bool
    {
        return $this->citations !== [];
    }
}
