<?php

namespace App\Services\Answering\Formatters;

use App\Services\Answering\Answer;
use App\Services\Answering\Citation;

class JsonFormatter
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Answer $answer): array
    {
        $sources = [];

        foreach ($answer->citations as $citation) {
            $sources[] = [
                'label' => $citation->label(),
                'path' => $citation->document->path,
                'line' => $citation->line,
                'excerpt' => $this->shorten($citation->excerpt),
            ];
        }

        return [
            'question' => $answer->question,
            'answer' => $answer->text,
            'source_count' => count($answer->citations),
            'sources' => $sources,
            'context_tokens' => $answer->contextTokens,
        ];
    }

    public function format(Answer $answer): string
    {
        return json_encode($this->toArray($answer), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function line(Citation $citation): string
    {
        return $citation->label().' '.$this->shorten($citation->excerpt);
    }

    private function shorten(string $text): string
    {
        return mb_strlen($text) > 60 ? mb_substr($text, 0, 57).'...' : $text;
    }
}
