<?php

namespace App\Services\Retrieval;

use App\Models\Document;

class ScoredDocument
{
    public function __construct(
        public readonly Document $document,
        public readonly float $score,
    ) {}
}
