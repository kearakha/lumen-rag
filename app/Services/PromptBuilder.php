<?php

namespace App\Services;

use App\Models\Chunk;
use Illuminate\Support\Collection;

class PromptBuilder
{
    /**
     * @param  Collection<int, Chunk>  $chunks
     */
    public function build(string $question, Collection $chunks): string
    {
        if ($chunks->isEmpty()) {
            return "Jawab pertanyaan berikut apa adanya:\n\n{$question}";
        }

        $context = $chunks
            ->map(fn ($chunk, $i) => '['.($i + 1).'] '.$chunk->content)
            ->implode("\n\n");

        return <<<PROMPT
        Jawab pertanyaan HANYA berdasarkan konteks di bawah ini. Kalau konteks tidak mengandung jawabannya, katakan kamu tidak tahu — jangan mengarang.
        Setiap klaim yang kamu tulis harus diakhiri penanda sumber sesuai nomor konteks yang kamu pakai, misalnya [1] atau [2].

        Konteks:
        {$context}

        Pertanyaan: {$question}
        PROMPT;
    }
}
