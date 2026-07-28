<?php

namespace App\Console\Commands;

use App\Models\Chunk;
use App\Services\Embedder;
use App\Services\LlmClient;
use App\Services\PromptBuilder;
use Illuminate\Console\Command;
use Pgvector\Laravel\Distance;

class EvalRun extends Command
{
    protected $signature = 'eval:run {--dataset=.docs/eval/dataset.json} {--out=} {--delay=13}';

    protected $description = 'Jalankan set pertanyaan eval lewat pipeline RAG dan simpan hasilnya ke markdown untuk digrading manual';

    public function handle(Embedder $embedder, PromptBuilder $promptBuilder, LlmClient $llm): int
    {
        $datasetPath = base_path($this->option('dataset'));
        $dataset = json_decode(file_get_contents($datasetPath), true);

        $outPath = $this->option('out') ?: base_path('.docs/eval/results-'.now()->format('Ymd-His').'.md');
        $delay = (int) $this->option('delay');

        file_put_contents($outPath, "| # | Pertanyaan | Dokumen diretrieve | Jawaban Lumen | Verdict (isi manual) |\n|---|---|---|---|---|\n");

        foreach ($dataset as $i => $item) {
            $question = $item['question'];

            $this->info(($i + 1).'. '.$question);

            if ($i > 0) {
                sleep($delay);
            }

            $questionEmbedding = $embedder->embed($question);

            $chunks = Chunk::query()
                ->with('document')
                ->nearestNeighbors('embedding', $questionEmbedding, Distance::Cosine)
                ->take(3)
                ->get();

            $prompt = $promptBuilder->build($question, $chunks);
            $answer = $llm->ask($prompt);

            $retrieved = $chunks->isEmpty()
                ? '(tidak ada)'
                : $chunks->map(fn (Chunk $c) => $c->document->filename)->unique()->implode(', ');

            $row = '| '.($i + 1).' | '.$this->escape($question).' | '.$this->escape($retrieved).' | '.$this->escape($answer).' |  |'."\n";

            file_put_contents($outPath, $row, FILE_APPEND);
        }

        $this->info('Hasil disimpan ke '.$outPath);

        return self::SUCCESS;
    }

    private function escape(string $text): string
    {
        return str_replace(["\n", '|'], [' ', '\\|'], $text);
    }
}
