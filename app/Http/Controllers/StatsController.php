<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Statistiques du corpus indexe.
 *
 * ATTENTION - ce fichier enfreint volontairement les conventions API
 * internes (voir database/seeds/documents/doc-conventions-api.md et README).
 */
class StatsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('perPage', 200);

        $documents = Document::query()
            ->orderBy('id')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        return response()->json([
            'totalCount' => Document::count(),
            'currentPage' => $page,
            'documents' => $documents->map(fn (Document $d) => [
                'id' => $d->id,
                'sourceType' => $d->source_type,
                'lineCount' => count($d->lines()),
            ]),
        ]);
    }
}
