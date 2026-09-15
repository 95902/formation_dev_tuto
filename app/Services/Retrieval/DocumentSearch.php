<?php

namespace App\Services\Retrieval;

use App\Models\Document;
use Illuminate\Support\Collection;

/**
 * Recherche par mots-cles dans les documents internes.
 *
 * Volontairement sans embeddings : le classement doit rester deterministe
 * pour que les tests soient reproductibles. La recherche vectorielle
 * (colonne pgvector deja prevue en base) viendra plus tard.
 */
class DocumentSearch
{
    public const MAX_RESULTS = 50;

    /**
     * @return Collection<int, ScoredDocument>
     */
    public function search(string $query, int $limit = 5): Collection
    {
        $terms = $this->tokenize($query);

        if ($terms === []) {
            return collect();
        }

        $scored = Document::all()
            ->map(fn (Document $doc) => new ScoredDocument($doc, $this->score($doc, $terms)))
            ->filter(fn (ScoredDocument $s) => $s->score > 0)
            ->sortByDesc(fn (ScoredDocument $s) => $s->score)
            ->values();

        return $this->dedupe($scored)->take($limit);
    }

    /**
     * @param  array<int, string>  $terms
     */
    public function score(Document $doc, array $terms): float
    {
        $haystack = mb_strtolower($doc->content);
        $title = mb_strtolower($doc->title);
        $score = 0.0;

        foreach ($terms as $term) {
            $score += substr_count($haystack, $term) * 1.0;
            $score += substr_count($title, $term) * 3.0;
        }

        return $score;
    }

    /**
     * @return array<int, string>
     */
    public function tokenize(string $query): array
    {
        $parts = preg_split('/[^\p{L}\p{N}]+/u', $query, -1, PREG_SPLIT_NO_EMPTY);

        $parts = array_map(fn (string $p) => mb_strtolower($p), $parts);

        return array_values(array_filter($parts, fn (string $p) => mb_strlen($p) > 2));
    }

    /**
     * Deux documents distincts peuvent porter le meme titre
     * (ex. un README par service). Seul le chemin les identifie.
     *
     * @param  Collection<int, ScoredDocument>  $scored
     * @return Collection<int, ScoredDocument>
     */
    private function dedupe(Collection $scored): Collection
    {
        $seen = [];
        return $scored->filter(function (ScoredDocument $s) use (&$seen) {
            $path = $s->document->path;
            if (isset($seen[$path])) {
                return false;
            }
            $seen[$path] = true;
            return true;
        })->values();
    }
}
