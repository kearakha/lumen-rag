<?php

namespace App\Jobs;

use App\Exceptions\GeminiQuotaExceededException;
use App\Models\Ask;
use App\Models\Chunk;
use App\Services\Embedder;
use App\Services\LlmClient;
use App\Services\PromptBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Pgvector\Laravel\Distance;
use Throwable;

class AskJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public int $askId,
    ) {}

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [5, 15, 30];
    }

    public function handle(Embedder $embedder, PromptBuilder $promptBuilder, LlmClient $llm): void
    {
        $ask = Ask::findOrFail($this->askId);
        $ask->update(['status' => 'processing']);

        try {
            $questionEmbedding = $embedder->embed($ask->question);

            $chunks = Chunk::query()
                ->with('document')
                ->where('document_id', $ask->document_id)
                ->nearestNeighbors('embedding', $questionEmbedding, Distance::Cosine)
                ->take(3)
                ->get();

            $prompt = $promptBuilder->build($ask->question, $chunks);
            $answer = $llm->ask($prompt);
        } catch (GeminiQuotaExceededException $e) {
            $this->fail($e);

            return;
        }

        $ask->update([
            'status' => 'done',
            'answer' => $answer,
            'sources' => $chunks->map(fn (Chunk $chunk) => [
                'document' => $chunk->document->filename,
                'excerpt' => $chunk->content,
            ])->all(),
        ]);
    }

    public function failed(Throwable $exception): void
    {
        $message = $exception instanceof GeminiQuotaExceededException
            ? $exception->getMessage()
            : 'Gagal mendapat jawaban dari LLM setelah beberapa percobaan. Coba lagi nanti.';

        Ask::whereKey($this->askId)->update([
            'status' => 'failed',
            'error' => $message,
        ]);
    }
}
