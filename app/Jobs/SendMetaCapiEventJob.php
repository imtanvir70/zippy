<?php

namespace App\Jobs;

use App\Services\MetaCapiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendMetaCapiEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [15, 60];

    public string $eventName;
    public array $eventData;

    public function __construct(string $eventName, array $eventData)
    {
        $this->eventName = $eventName;
        $this->eventData = $eventData;
    }

    public function handle(MetaCapiService $capiService): void
    {
        try {
            $capiService->sendEvent($this->eventName, $this->eventData);
        } catch (Throwable $e) {
            Log::channel('single')->error('SendMetaCapiEventJob Failed: ' . $e->getMessage(), [
                'event_name' => $this->eventName,
                'event_id' => $this->eventData['event_id'] ?? null,
                'order_number' => $this->eventData['order_number'] ?? null,
            ]);
            throw $e;
        }
    }
}
