<?php

namespace App\Http\Controllers;

use App\Models\Assure;
use App\Models\Contrat;
use App\Models\Document;
use App\Models\Sinistre;
use Database\Seeders\PortefeuilleSeeder;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $ancre = \Carbon\Carbon::parse(PortefeuilleSeeder::ANCRE);

        return view('dashboard', [
            'assures' => Assure::count(),
            'contratsActifs' => Contrat::actifs()->count(),
            'documents' => Document::count(),
            'enInstruction' => Sinistre::enInstruction()->count(),
            'clos' => Sinistre::where('statut', 'cloture')->count(),
            'refuses' => Sinistre::where('statut', 'refuse')->count(),
            'encours' => Sinistre::enInstruction()->sum('montant_estime_cents'),
            'declaresCetteSemaine' => Sinistre::where('declare_le', '>=', $ancre->copy()->subDays(7))->count(),
            'derniers' => Sinistre::with('contrat.assure')
                ->orderByDesc('declare_le')
                ->limit(8)
                ->get(),
            'parNature' => Sinistre::query()
                ->selectRaw('nature, count(*) as total')
                ->groupBy('nature')
                ->orderByDesc('total')
                ->get(),
        ]);
    }
}
