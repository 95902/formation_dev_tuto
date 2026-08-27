<?php

namespace Database\Seeders;

use App\Models\Document;
use Illuminate\Database\Seeder;

/**
 * Charge le corpus de documents internes depuis database/seeds/documents.
 *
 * Le type de source est deduit du prefixe du nom de fichier :
 * wiki-*, doc-*, README-* (traite comme du code).
 */
class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $dir = database_path('seeds/documents');

        foreach (glob($dir.'/*.md') as $path) {
            $name = basename($path, '.md');
            $content = file_get_contents($path);

            Document::updateOrCreate(
                ['path' => 'seeds/documents/'.$name.'.md'],
                [
                    'title' => $this->titleFrom($content, $name),
                    'source_type' => $this->sourceTypeFrom($name),
                    'content' => $content,
                ],
            );
        }
    }

    private function titleFrom(string $content, string $fallback): string
    {
        foreach (preg_split('/\r\n|\n|\r/', $content) as $line) {
            if (str_starts_with($line, '# ')) {
                return trim(substr($line, 2));
            }
        }

        return $fallback;
    }

    private function sourceTypeFrom(string $name): string
    {
        return match (true) {
            str_starts_with($name, 'wiki-') => 'wiki',
            str_starts_with($name, 'README') => 'code',
            default => 'doc',
        };
    }
}
