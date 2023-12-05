<?php

namespace App\Jobs;

use App\Models\Source;
use App\Services\SourceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ParseSource implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Source $source,
        public string $mode = "prod"
    ){}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if($this->mode == "prod") {

        } else {
            $data = SourceService::test($this->source);
        }

        Log::info("Parsowanie gotowe - " . $this->source->name, $data);
    }

    public function failed(\Throwable $exception): void
    {
        Log::info("Parsowanie error - " . $this->source->name, [
            'message' => $exception->getMessage().
            'file' -> $exception->getFile(),
            'line' -> $exception->getLine(),
            'code' => $exception->getCode(),
        ]);
    }
}
