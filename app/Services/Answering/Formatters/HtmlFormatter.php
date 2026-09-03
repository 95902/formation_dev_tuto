<?php

namespace App\Services\Answering\Formatters;

use App\Services\Answering\Citation;

class HtmlFormatter extends AbstractTextFormatter
{
    protected function renderQuestion(string $question): string
    {
        return '<p><strong>Question :</strong> '.e($question)."</p>\n";
    }

    protected function renderBody(string $text): string
    {
        return '<p>'.e($text)."</p>\n";
    }

    protected function renderNoSources(): string
    {
        return "<p>Aucune source.</p>\n";
    }

    protected function renderSourcesHeader(int $count): string
    {
        $header = $count === 1 ? "<p><strong>1 source :</strong></p>\n" : "<p><strong>{$count} sources :</strong></p>\n";

        return $header."<ul>\n";
    }

    protected function renderCitationLine(Citation $citation): string
    {
        return '<li><code>'.e($citation->label()).'</code> '.e($this->shorten($citation->excerpt))."</li>\n";
    }

    protected function renderCitationsClose(): string
    {
        return "</ul>\n";
    }

    protected function renderFooter(int $contextTokens): string
    {
        return '<p><em>[contexte ~'.$contextTokens." tokens]</em></p>\n";
    }
}
