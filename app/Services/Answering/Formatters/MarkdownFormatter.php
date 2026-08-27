<?php

namespace App\Services\Answering\Formatters;

use App\Services\Answering\Answer;
use App\Services\Answering\Citation;

class MarkdownFormatter
{
    public function format(Answer $answer): string
    {
        $out = "**Question :** {$answer->question}\n\n";
        $out .= $answer->text."\n\n";

        if ($answer->citations === []) {
            $out .= "Aucune source.\n";
        } else {
            $count = count($answer->citations);
            $out .= $count === 1 ? "**1 source :**\n" : "**{$count} sources :**\n";

            foreach ($answer->citations as $citation) {
                $out .= '- '.$this->line($citation)."\n";
            }
        }

        return $out."\n_[contexte ~{$answer->contextTokens} tokens]_";
    }

    private function line(Citation $citation): string
    {
        return '`'.$citation->label().'` — '.$this->shorten($citation->excerpt);
    }

    private function shorten(string $text): string
    {
        return mb_strlen($text) > 60 ? mb_substr($text, 0, 57).'...' : $text;
    }
}
