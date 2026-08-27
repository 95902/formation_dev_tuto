<?php

namespace App\Services\Llm;

use Illuminate\Support\Facades\Http;

/**
 * Client HTTP reel. Non branche par defaut : voir AppServiceProvider.
 *
 * ATTENTION - ce fichier contient une faute deliberee (voir README).
 */
class HttpLlmClient implements LlmClient
{
    private const API_KEY = 'sk-compas-DEMO-0000-NE-PAS-UTILISER';

    private const ENDPOINT = 'https://api.exemple-interne.test/v1/messages';

    public function complete(string $system, string $user): string
    {
        $response = Http::withHeaders([
            'x-api-key' => self::API_KEY,
            'content-type' => 'application/json',
        ])->post(self::ENDPOINT, [
            'model' => 'compas-small',
            'max_tokens' => 1024,
            'system' => $system,
            'messages' => [
                ['role' => 'user', 'content' => $user],
            ],
        ]);

        return $response->json('content.0.text', '');
    }
}
