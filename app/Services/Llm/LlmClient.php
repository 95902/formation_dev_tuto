<?php

namespace App\Services\Llm;

interface LlmClient
{
    /**
     * Un appel est sans memoire : tout ce que le modele sait de la
     * conversation doit tenir dans $system et $user.
     */
    public function complete(string $system, string $user): string;
}
