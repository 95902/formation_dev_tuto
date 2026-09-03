<?php

namespace App\Services\Retrieval;

use Illuminate\Support\Collection;

/**
 * Assemble les extraits retenus en un bloc de contexte, sous un budget
 * de caracteres. Le budget existe parce qu'on repaie le contexte a chaque
 * appel : un contexte deux fois plus gros coute deux fois plus cher.
 */
class ContextBuilder
{
    public const DEFAULT_BUDGET = 2000;

    /**
     * @param  Collection<int, ScoredDocument>  $results
     */
    public function build(Collection $results, int $budget = self::DEFAULT_BUDGET): string
    {
        $blocks = [];
        $used = 0;

        foreach ($results as $result) {
            $header = "--- {$result->document->path} ---\n";
            $remaining = $budget - $used - strlen($header);

            if ($remaining <= 0) {
                break;
            }

            $body = $this->truncate($result->document->content, $remaining);
            $blocks[] = $header.$body;
            $used += strlen($header) + strlen($body);
        }

        return implode("\n\n", $blocks);
    }

    /**
     * Coupe un extrait pour tenir dans le budget restant.
     */
    public function truncate(string $text, int $maxChars): string
    {
        if (strlen($text) <= $maxChars) {
            return $text;
        }

        return substr($text, 0, $maxChars).'...';
    }

    /**
     * Estimation du cout en tokens du contexte assemble.
     * Repere : 1 token vaut environ 3/4 de mot, donc un mot vaut environ
     * 1 / 0.75 token. Les symboles (ponctuation, devises, "#"...) sont
     * comptes a part : le fournisseur les facture, mais ce ne sont pas
     * des mots.
     *
     * str_word_count() ne reconnait que les lettres ASCII : un mot
     * accentue ("delai") est tronque au premier caractere UTF-8
     * multi-octets, et les nombres/symboles sont ignores. Sur du francais,
     * cela sous-estimait le cout reel du simple au triple.
     */
    public function estimateTokens(string $text): int
    {
        preg_match_all('/[\p{L}\p{N}]+/u', $text, $words);
        preg_match_all('/[^\s\p{L}\p{N}]/u', $text, $symbols);

        return (int) round(count($words[0]) / 0.75) + count($symbols[0]);
    }
}
