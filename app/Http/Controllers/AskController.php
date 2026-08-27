<?php

namespace App\Http\Controllers;

use App\Services\Answering\Answerer;
use App\Services\Answering\Formatters\JsonFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AskController extends Controller
{
    public function __invoke(Request $request, Answerer $answerer, JsonFormatter $formatter): JsonResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'min:3', 'max:500'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:20'],
        ]);

        $answer = $answerer->ask($data['question'], $data['limit'] ?? 5);

        return response()->json($formatter->toArray($answer));
    }
}
