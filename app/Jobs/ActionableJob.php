<?php

namespace App\Jobs;

use App\Models\Action;
use App\Models\Enums\ActionStatus;
use App\Models\Interfaces\Actionable;
use App\Models\Source;
use App\Services\SourceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

abstract class ActionableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Action $action;

    abstract public function run() : array;

    abstract public function getActionable(): Actionable;

    abstract public function getType(): string;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $data = $this->run();

        $info = null;

        if(isset($data['info'])) {
            $info = $data['info'];
            unset($data['info']);
        }

        Action::where('job_uuid', $this->job->uuid())->firstOrFail()->update([
            'status' => ActionStatus::SUCCESS,
            'data' => json_encode($data),
            'info' => $info
        ]);
    }
}