<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssureRequest;
use App\Models\Assure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AssureController extends Controller
{
    public function index(Request $request): View
    {
        $assures = Assure::query()
            ->withCount('contrats')
            ->when($request->filled('q'), function ($query) use ($request) {
                $terme = '%'.$request->string('q').'%';

                $query->where(fn ($q) => $q
                    ->where('nom', 'like', $terme)
                    ->orWhere('prenom', 'like', $terme)
                    ->orWhere('reference', 'like', $terme)
                    ->orWhere('ville', 'like', $terme));
            })
            ->orderBy('nom')
            ->paginate(20)
            ->withQueryString();

        return view('assures.index', compact('assures'));
    }

    public function create(): View
    {
        return view('assures.form', ['assure' => new Assure]);
    }

    public function store(AssureRequest $request): RedirectResponse
    {
        $assure = Assure::create($request->validated() + [
            'reference' => $this->prochaineReference(),
        ]);

        return redirect()
            ->route('assures.show', $assure)
            ->with('ok', "Assuré {$assure->reference} créé.");
    }

    public function show(Assure $assure): View
    {
        $assure->load(['contrats.garanties', 'contrats.sinistres']);

        return view('assures.show', compact('assure'));
    }

    public function edit(Assure $assure): View
    {
        return view('assures.form', compact('assure'));
    }

    public function update(AssureRequest $request, Assure $assure): RedirectResponse
    {
        $assure->update($request->validated());

        return redirect()
            ->route('assures.show', $assure)
            ->with('ok', 'Fiche mise à jour.');
    }

    public function destroy(Assure $assure): RedirectResponse
    {
        if ($assure->contrats()->exists()) {
            return back()->with('erreur', 'Impossible : cet assuré porte encore des contrats.');
        }

        $reference = $assure->reference;
        $assure->delete();

        return redirect()
            ->route('assures.index')
            ->with('ok', "Assuré {$reference} supprimé.");
    }

    private function prochaineReference(): string
    {
        return sprintf('ASS-2026-%03d', Assure::max('id') + 1);
    }
}
