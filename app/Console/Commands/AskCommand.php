<?php

namespace App\Console\Commands;

use App\Services\Answering\Answerer;
use App\Services\Answering\Formatters\CliFormatter;
use Illuminate\Console\Command;

class AskCommand extends Command
{
    protected $signature = 'compas:ask {question* : La question posee} {--limit=5 : Nombre de documents retenus}';

    protected $description = 'Interroge la documentation interne et affiche la reponse avec ses sources';

    public function handle(Answerer $answerer, CliFormatter $formatter): int
    {
        $question = implode(' ', $this->argument('question'));

        $answer = $answerer->ask($question, (int) $this->option('limit'));

        $this->line($formatter->format($answer));

        return $answer->hasSources() ? self::SUCCESS : self::FAILURE;
    }
}
