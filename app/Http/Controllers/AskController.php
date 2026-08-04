<?php

namespace App\Http\Controllers;

use App\Http\Requests\AskRequest;
use App\Jobs\AskJob;
use App\Models\Ask;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AskController extends Controller
{
    public function store(AskRequest $request): JsonResponse
    {
        $ask = Ask::create([
            'document_id' => $request->validated('document_id'),
            'question' => $request->validated('question'),
            'status' => 'pending',
        ]);

        AskJob::dispatch($ask->id);

        return response()->json(['ask_id' => $ask->id], 202);
    }

    public function stream(int $askId): StreamedResponse
    {
        return response()->stream(function () use ($askId) {
            $ask = Ask::find($askId);

            if (! $ask) {
                $this->emit('error', ['message' => 'Pertanyaan tidak ditemukan.']);

                return;
            }

            $deadline = now()->addSeconds(60);

            while (now()->lessThan($deadline)) {
                $ask->refresh();

                if ($ask->status === 'done') {
                    $this->emit('done', [
                        'answer' => $ask->answer,
                        'sources' => $ask->sources,
                    ]);

                    return;
                }

                if ($ask->status === 'failed') {
                    $this->emit('error', ['message' => $ask->error]);

                    return;
                }

                $this->emit('status', ['status' => $ask->status]);
                usleep(500_000);
            }

            $this->emit('error', ['message' => 'Waktu tunggu habis. Jawaban belum siap.']);
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function emit(string $event, array $data): void
    {
        echo "event: {$event}\n";
        echo 'data: '.json_encode($data)."\n\n";
        ob_flush();
        flush();
    }
}
