<?php

namespace Tests\Unit\Formatters;

use App\Services\Answering\Formatters\CliFormatter;
use App\Services\Answering\Formatters\Concerns\ShortensExcerpts;
use App\Services\Answering\Formatters\HtmlFormatter;
use App\Services\Answering\Formatters\JsonFormatter;
use App\Services\Answering\Formatters\MarkdownFormatter;
use App\Services\Answering\Formatters\SlackFormatter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class FormatterDuplicationTest extends TestCase
{
    #[DataProvider('formatterClasses')]
    public function test_it_reuses_the_shared_shorten_implementation(string $class): void
    {
        $this->assertContains(
            ShortensExcerpts::class,
            class_uses_recursive($class),
            "{$class} devrait reutiliser ShortensExcerpts au lieu de declarer son propre shorten()."
        );
    }

    public function test_json_formatter_has_no_dead_line_method(): void
    {
        $this->assertFalse(
            (new ReflectionClass(JsonFormatter::class))->hasMethod('line'),
            'JsonFormatter::line() est du code mort et devrait etre supprime.'
        );
    }

    public static function formatterClasses(): array
    {
        return [
            [CliFormatter::class],
            [HtmlFormatter::class],
            [JsonFormatter::class],
            [MarkdownFormatter::class],
            [SlackFormatter::class],
        ];
    }
}
