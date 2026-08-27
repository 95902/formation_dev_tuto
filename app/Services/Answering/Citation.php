<?php

namespace App\Services\Answering;

use App\Models\Document;

class Citation
{
    public function __construct(
        public readonly Document $document,
        public readonly string $excerpt,
        public readonly int $line,
    ) {}

    public function label(): string
    {
        return "{$this->document->path}:{$this->line}";
    }

    /**
     * Localise l'extrait dans le document et construit la citation.
     * Le numero de ligne affiche doit etre celui qu'un editeur affiche,
     * donc la premiere ligne du fichier est la ligne 1.
     */
    public static function locate(Document $document, string $excerpt): self
    {
        $position = mb_strpos($document->content, $excerpt);

        if ($position === false) {
            return new self($document, $excerpt, 1);
        }

        $before = mb_substr($document->content, 0, $position);
        $line = substr_count($before, "\n");

        return new self($document, $excerpt, $line);
    }
}
