<?php

namespace App\Services;

use App\Exceptions\GeminiQuotaExceededException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class LlmClient
{
    public function ask(string $question): string
    {
        if (config('services.gemini.via_sanmon')) {
            return $this->askViaSanmon($question);
        }

        $model = config('services.gemini.chat_model');

        $response = Http::timeout(30)
            ->withHeaders(['x-goog-api-key' => config('services.gemini.key')])
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                'contents' => [
                    ['parts' => [['text' => $question]]],
                ],
            ]);

        $this->guard($response);

        return $response->json('candidates.0.content.parts.0.text');
    }

    /**
     * Jalur gateway Sanmon: format OpenAI chat-completions, auth virtual key.
     * Sanmon meneruskan apa adanya ke endpoint OpenAI-compat Gemini.
     */
    private function askViaSanmon(string $question): string
    {
        $response = Http::timeout(30)
            ->withToken(config('services.sanmon.key'))
            ->post(rtrim(config('services.sanmon.base_url'), '/').'/v1/chat/completions', [
                'model' => config('services.gemini.chat_model'),
                'messages' => [
                    ['role' => 'user', 'content' => $question],
                ],
            ]);

        $this->guard($response);

        return $response->json('choices.0.message.content');
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
            throw new RuntimeException('LLM request failed: '.$response->body());
        }
    }
}
