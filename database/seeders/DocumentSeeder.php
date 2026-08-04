<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Services\Chunker;
use App\Services\Embedder;
use Illuminate\Database\Seeder;

class DocumentSeeder extends Seeder
{
    public function run(Chunker $chunker, Embedder $embedder): void
    {
        $path = base_path('.docs/eval/test-document.txt');
        $text = trim(file_get_contents($path));

        $document = Document::create([
            'filename' => 'aurion-dynamics.txt',
            'content' => $text,
        ]);

        $chunks = $chunker->chunk($text);
        $embeddings = $embedder->embedBatch($chunks);

        foreach ($chunks as $i => $chunkText) {
            $document->chunks()->create([
                'content' => $chunkText,
                'embedding' => $embeddings[$i],
            ]);
        }
    }
}
