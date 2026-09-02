<?php

namespace Tests\Feature;

use App\Services\Embedder;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EmbedderSanmonTest extends TestCase
{
    public function test_via_sanmon_hits_gateway_and_maps_data_in_order(): void
    {
        config([
            'services.gemini.via_sanmon' => true,
            'services.sanmon.base_url' => 'http://sanmon.test:8777',
            'services.sanmon.key' => 'sk-sanmon-abc',
        ]);

        // Layer OpenAI-compat Gemini balikin data[] urut input, tanpa `index`.
        Http::fake([
            'sanmon.test:8777/v1/embeddings' => Http::response([
                'data' => [
                    ['embedding' => [0.1, 0.2]],
                    ['embedding' => [0.4, 0.5]],
                ],
            ]),
        ]);

        $out = (new Embedder)->embedBatch(['a', 'b']);

        $this->assertSame([[0.1, 0.2], [0.4, 0.5]], $out);
        Http::assertSent(function ($request) {
            return $request->url() === 'http://sanmon.test:8777/v1/embeddings'
                && $request->hasHeader('Authorization', 'Bearer sk-sanmon-abc')
                && $request['input'] === ['a', 'b']
                && $request['dimensions'] === 768;
        });
    }

    public function test_native_path_still_hits_gemini_when_flag_off(): void
    {
        config([
            'services.gemini.via_sanmon' => false,
            'services.gemini.key' => 'gemini-key',
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'embeddings' => [['values' => [0.9, 0.8]]],
            ]),
        ]);

        $out = (new Embedder)->embedBatch(['a']);

        $this->assertSame([[0.9, 0.8]], $out);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'batchEmbedContents'));
    }
}
