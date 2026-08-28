<?php

namespace App\Http\Controllers;

use App\Models\Contrat;
use App\Models\Sinistre;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SinistreController extends Controller
{
    public function index(Request $request): View
    {
        $sinistres = Sinistre::query()
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->string('statut')))
            ->when($request->filled('nature'), fn ($q) => $q->where('nature', $request->string('nature')))
            ->when($request->filled('du'), fn ($q) => $q->where('declare_le', '>=', $request->string('du')))
            ->when($request->filled('au'), fn ($q) => $q->where('declare_le', '<=', $request->string('au')))
            ->when($request->filled('q'), function ($query) use ($request) {
                $terme = '%'.$request->string('q').'%';

                $query->where(fn ($q) => $q
                    ->where('reference', 'like', $terme)
                    ->orWhere('description', 'like', $terme));
            })
            ->orderByDesc('declare_le')
            ->paginate(25)
            ->withQueryString();

        return view('sinistres.index', compact('sinistres'));
    }

    public function create(Request $request): View
    {
        return view('sinistres.form', [
            'sinistre' => new Sinistre(['contrat_id' => $request->integer('contrat_id') ?: null]),
            'contrats' => Contrat::with('assure')->orderBy('reference')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->enCentimes($request);

        $valide = $request->validate($this->regles());

        $valide['reference'] = $this->prochaineReference();

        $sinistre = Sinistre::create($valide);

        return redirect()
            ->route('sinistres.show', $sinistre)
            ->with('ok', "Dossier {$sinistre->reference} ouvert.");
    }

    public function show(Sinistre $sinistre): View
    {
        $sinistre->load(['contrat.assure', 'contrat.garanties', 'pieces']);

        return view('sinistres.show', compact('sinistre'));
    }

    public function edit(Sinistre $sinistre): View
    {
        return view('sinistres.form', [
            'sinistre' => $sinistre,
            'contrats' => Contrat::with('assure')->orderBy('reference')->get(),
        ]);
    }

    public function update(Request $request, Sinistre $sinistre): RedirectResponse
    {
        $this->enCentimes($request);

        $request->validate($this->regles());

        $sinistre->update($request->all());

        return redirect()
            ->route('sinistres.show', $sinistre)
            ->with('ok', 'Dossier mis à jour.');
    }

    public function destroy(Sinistre $sinistre): RedirectResponse
    {
        $contrat = $sinistre->contrat;
        $reference = $sinistre->reference;

        $sinistre->delete();

        return redirect()
            ->route('contrats.show', $contrat)
            ->with('ok', "Dossier {$reference} supprimé.");
    }

    /**
     * Le formulaire saisit des euros ; la base ne connait que des centimes.
     */
    private function enCentimes(Request $request): void
    {
        if ($request->filled('montant_estime')) {
            $request->merge([
                'montant_estime_cents' => (int) round($request->float('montant_estime') * 100),
            ]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function regles(): array
    {
        return [
            'contrat_id' => ['required', 'exists:contrats,id'],
            'nature' => ['required', Rule::in(array_keys(Sinistre::NATURES))],
            'survenu_le' => ['required', 'date'],
            'declare_le' => ['required', 'date', 'after_or_equal:survenu_le'],
            'description' => ['required', 'string', 'min:10', 'max:2000'],
            'statut' => ['required', Rule::in(array_keys(Sinistre::STATUTS))],
            'montant_estime_cents' => ['required', 'integer', 'min:0', 'max:100000000'],
            'gestionnaire' => ['nullable', 'string', 'max:100'],
        ];
    }

    private function prochaineReference(): string
    {
        return sprintf('SIN-2026-%05d', Sinistre::max('id') + 1);
    }
}
