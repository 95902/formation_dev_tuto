<?php

namespace App\Services\Answering\Formatters;

use App\Services\Answering\Answer;
use App\Services\Answering\Citation;

class HtmlFormatter
{
    public function format(Answer $answer): string
    {
        $out = '<p><strong>Question :</strong> '.e($answer->question)."</p>\n";
        $out .= '<p>'.e($answer->text)."</p>\n";

        if ($answer->citations === []) {
            $out .= "<p>Aucune source.</p>\n";
        } else {
            $count = count($answer->citations);
            $out .= $count === 1 ? "<p><strong>1 source :</strong></p>\n" : "<p><strong>{$count} sources :</strong></p>\n";
            $out .= "<ul>\n";

            foreach ($answer->citations as $citation) {
                $out .= '<li>'.$this->line($citation)."</li>\n";
            }

            $out .= "</ul>\n";
        }

        return $out.'<p><em>[contexte ~'.$answer->contextTokens." tokens]</em></p>\n";
    }

    private function line(Citation $citation): string
    {
        return '<code>'.e($citation->label()).'</code> '.e($this->shorten($citation->excerpt));
    }

    private function shorten(string $text): string
    {
        return mb_strlen($text) > 60 ? mb_substr($text, 0, 57).'...' : $text;
    }
}
