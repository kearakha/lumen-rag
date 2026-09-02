<?php

namespace Tests\Feature;

use App\Services\LlmClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LlmClientSanmonTest extends TestCase
{
    public function test_via_sanmon_hits_gateway_and_parses_openai_shape(): void
    {
        config([
            'services.gemini.via_sanmon' => true,
            'services.gemini.chat_model' => 'gemini-flash-latest',
            'services.sanmon.base_url' => 'http://sanmon.test:8777',
            'services.sanmon.key' => 'sk-sanmon-abc',
        ]);

        Http::fake([
            'sanmon.test:8777/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => 'jawaban lewat gateway']]],
            ]),
        ]);

        $answer = (new LlmClient)->ask('halo?');

        $this->assertSame('jawaban lewat gateway', $answer);
        Http::assertSent(function ($request) {
            return $request->url() === 'http://sanmon.test:8777/v1/chat/completions'
                && $request->hasHeader('Authorization', 'Bearer sk-sanmon-abc')
                && $request['messages'][0]['content'] === 'halo?';
        });
    }

    public function test_native_path_still_hits_gemini_when_flag_off(): void
    {
        config([
            'services.gemini.via_sanmon' => false,
            'services.gemini.key' => 'gemini-key',
            'services.gemini.chat_model' => 'gemini-flash-latest',
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => 'jawaban native']]]]],
            ]),
        ]);

        $answer = (new LlmClient)->ask('halo?');

        $this->assertSame('jawaban native', $answer);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'generativelanguage.googleapis.com'));
    }
}
