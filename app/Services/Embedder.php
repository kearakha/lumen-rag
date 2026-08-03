<?php

namespace App\Services;

use App\Exceptions\GeminiQuotaExceededException;
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

        if ($response->status() === 429 && str_contains($response->body(), 'PerDay')) {
            throw new GeminiQuotaExceededException('Kuota harian Gemini API sudah habis. Coba lagi besok setelah kuota reset.');
        }

        if ($response->failed()) {
            throw new RuntimeException('Embedding request failed: '.$response->body());
        }

        return array_map(
            fn (array $embedding) => $embedding['values'],
            $response->json('embeddings')
        );
    }

    /**
     * @return array<int, float>
     */
    public function embed(string $text): array
    {
        return $this->embedBatch([$text])[0];
    }
}
