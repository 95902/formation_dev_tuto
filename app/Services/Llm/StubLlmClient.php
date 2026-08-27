<?php

namespace App\Services\Llm;

/**
 * Client par defaut du bac a sable : aucune requete reseau, aucune cle.
 *
 * Il ne « comprend » rien. Il rend les premieres phrases utiles du contexte,
 * ce qui suffit a rendre la chaine testable de bout en bout de facon
 * deterministe.
 */
class StubLlmClient implements LlmClient
{
    public const QUESTION_MARKER = "\nQUESTION : ";

    public const CONTEXT_MARKER = "CONTEXTE :\n";

    private const MAX_SENTENCES = 3;

    public function complete(string $system, string $user): string
    {
        [$context, $question] = $this->split($user);

        $sentences = $this->sentences($context);

        if ($sentences === []) {
            return "Je n'ai rien trouve dans la documentation interne au sujet de : {$question}";
        }

        return "D'apres la documentation interne : "
            .implode(' ', array_slice($sentences, 0, self::MAX_SENTENCES));
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function split(string $prompt): array
    {
        $at = mb_strrpos($prompt, self::QUESTION_MARKER);

        if ($at === false) {
            return [$prompt, ''];
        }

        $context = mb_substr($prompt, 0, $at);
        $question = trim(mb_substr($prompt, $at + mb_strlen(self::QUESTION_MARKER)));

        if (str_starts_with($context, self::CONTEXT_MARKER)) {
            $context = mb_substr($context, mb_strlen(self::CONTEXT_MARKER));
        }

        return [$context, $question];
    }

    /**
     * Les lignes « --- chemin --- » delimitent les blocs : ce sont des
     * metadonnees, pas du contenu citable.
     *
     * @return array<int, string>
     */
    private function sentences(string $context): array
    {
        $body = array_filter(
            preg_split('/\r\n|\n|\r/', $context),
            fn (string $line) => ! str_starts_with(trim($line), '---'),
        );

        $flat = trim((string) preg_replace('/\s+/u', ' ', implode(' ', $body)));

        if ($flat === '') {
            return [];
        }

        $parts = preg_split('/(?<=[.!?])\s+/u', $flat, -1, PREG_SPLIT_NO_EMPTY);

        return array_values(array_filter(
            $parts,
            fn (string $p) => mb_strlen($p) > 30,
        ));
    }
}
