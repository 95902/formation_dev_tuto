<?php

namespace App\Services\Answering\Formatters\Concerns;

trait ShortensExcerpts
{
    protected function shorten(string $text): string
    {
        return mb_strlen($text) > 60 ? mb_substr($text, 0, 57).'...' : $text;
    }
}
