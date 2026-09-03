<?php

namespace App\Services\Answering\Formatters;

use App\Services\Answering\Answer;
use App\Services\Answering\Citation;
use App\Services\Answering\Formatters\Concerns\ShortensExcerpts;

abstract class AbstractTextFormatter
{
    use ShortensExcerpts;

    public function format(Answer $answer): string
    {
        $out = $this->renderQuestion($answer->question);
        $out .= $this->renderBody($answer->text);

        if ($answer->citations === []) {
            $out .= $this->renderNoSources();
        } else {
            $out .= $this->renderSourcesHeader(count($answer->citations));

            foreach ($answer->citations as $citation) {
                $out .= $this->renderCitationLine($citation);
            }

            $out .= $this->renderCitationsClose();
        }

        return $out.$this->renderFooter($answer->contextTokens);
    }

    abstract protected function renderQuestion(string $question): string;

    abstract protected function renderBody(string $text): string;

    abstract protected function renderNoSources(): string;

    abstract protected function renderSourcesHeader(int $count): string;

    abstract protected function renderCitationLine(Citation $citation): string;

    abstract protected function renderFooter(int $contextTokens): string;

    protected function renderCitationsClose(): string
    {
        return '';
    }
}
