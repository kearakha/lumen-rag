<?php

namespace App\Services;

use App\Exceptions\GeminiQuotaExceededException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class Embedder
{
    private const MODEL = 'gemini-embedding-001';

    private const DIMENSIONS = 768;

    /**
     * @param  array<int, string>  $texts
     * @return array<int, array<int, float>>
     */
    public function embedBatch(array $texts): array
    {
        if (config('services.gemini.via_sanmon')) {
            return $this->embedBatchViaSanmon($texts);
        }

        $response = Http::timeout(30)
            ->withHeaders(['x-goog-api-key' => config('services.gemini.key')])
            ->post(
                'https://generativelanguage.googleapis.com/v1beta/models/'.self::MODEL.':batchEmbedContents',
                [
                    'requests' => array_map(fn (string $text) => [
                        'model' => 'models/'.self::MODEL,
                        'content' => ['parts' => [['text' => $text]]],
                        'outputDimensionality' => self::DIMENSIONS,
                    ], $texts),
                ]
            );

        $this->guard($response);

        return array_map(
            fn (array $embedding) => $embedding['values'],
            $response->json('embeddings')
        );
    }

    /**
     * Jalur gateway Sanmon: format OpenAI embeddings, auth virtual key.
     * Sanmon meneruskan apa adanya ke endpoint OpenAI-compat Gemini, yang
     * mengembalikan data[] urut input (tanpa field `index` a la OpenAI asli).
     *
     * @param  array<int, string>  $texts
     * @return array<int, array<int, float>>
     */
    private function embedBatchViaSanmon(array $texts): array
    {
        $response = Http::timeout(30)
            ->withToken(config('services.sanmon.key'))
            ->post(rtrim(config('services.sanmon.base_url'), '/').'/v1/embeddings', [
                'model' => self::MODEL,
                'input' => array_values($texts),
                'dimensions' => self::DIMENSIONS,
            ]);

        $this->guard($response);

        return array_map(
            fn (array $row) => $row['embedding'],
            $response->json('data')
        );
    }

    /**
     * 429 ber-"PerDay" = kuota harian Gemini habis (diteruskan verbatim oleh
     * Sanmon). 429 lain lewat Sanmon = rate limit per key, 402 = budget habis —
     * dua-duanya jatuh ke RuntimeException biasa.
     */
    private function guard(Response $response): void
    {
        if ($response->status() === 429 && str_contains($response->body(), 'PerDay')) {
            throw new GeminiQuotaExceededException('Kuota harian Gemini API sudah habis. Coba lagi besok setelah kuota reset.');
        }

        if ($response->failed()) {
            throw new RuntimeException('Embedding request failed: '.$response->body());
        }
    }

    /**
     * @return array<int, float>
     */
    public function embed(string $text): array
    {
        return $this->embedBatch([$text])[0];
    }
}
