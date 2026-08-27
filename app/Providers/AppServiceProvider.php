<?php

namespace App\Providers;

use App\Services\Llm\LlmClient;
use App\Services\Llm\StubLlmClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Le bac a sable n'appelle aucune API : voir README, section
        // « Aucune cle n'est necessaire ».
        $this->app->bind(LlmClient::class, StubLlmClient::class);
    }

    public function boot(): void
    {
        //
    }
}
