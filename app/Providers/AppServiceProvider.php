<?php

namespace App\Providers;

use App\Services\Llm\LlmClient;
use App\Services\Llm\StubLlmClient;
use App\Support\QueryLog;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Le bac a sable n'appelle aucune API : voir README, section
        // « Aucune cle n'est necessaire ».
        $this->app->bind(LlmClient::class, StubLlmClient::class);

        $this->app->singleton(QueryLog::class);
    }

    public function boot(): void
    {
        Paginator::defaultView('pagination');

        if (! config('app.debug')) {
            return;
        }

        // Compteur de requetes affiche en bas de chaque page. Une page qui
        // en declenche cent doit se voir sans avoir a installer d'outil.
        $log = $this->app->make(QueryLog::class);

        DB::listen(fn (QueryExecuted $query) => $log->record($query->time));

        View::share('queryLog', $log);
    }
}
