<?php

namespace App\Services\Answering\Formatters;

use App\Services\Answering\Citation;

class CliFormatter extends AbstractTextFormatter
{
    protected function renderQuestion(string $question): string
    {
        return "Question : {$question}\n\n";
    }

    protected function renderBody(string $text): string
    {
        return wordwrap($text, 78)."\n\n";
    }

    protected function renderNoSources(): string
    {
        return "Aucune source.\n";
    }

    protected function renderSourcesHeader(int $count): string
    {
        return $count === 1 ? "1 source :\n" : "{$count} sources :\n";
    }

    protected function renderCitationLine(Citation $citation): string
    {
        return '  - '.$citation->label().'  '.$this->shorten($citation->excerpt)."\n";
    }

    protected function renderFooter(int $contextTokens): string
    {
        return "\n[contexte ~{$contextTokens} tokens]";
    }
}
