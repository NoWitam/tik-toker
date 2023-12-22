<?php

namespace App\Providers;

use App\Jobs\ActionableJob;
use App\Models\Action;
use App\Models\Enums\ActionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\Events\JobQueued;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();

        Event::listen(JobQueued::class, function($event) {

            if($event->job instanceof ActionableJob) {

                $event->job->getActionable()->actions()->create([
                    'type' => $event->job->getType(),
                    'job_uuid' => $event->payload()['uuid'],
                    'user_id' => auth()->check() ? auth()->user()->id : null
                ]);

            }

        });

        Queue::before(function ($event) {

            $job = $this->getJobInstance($event);
            if($job instanceof ActionableJob) {

                $action = Action::where('job_uuid', $event->job->uuid())->firstOrFail();
                $action->update([
                    'status' => ActionStatus::PROCESSING,
                    'attempts' => $action->attempts + 1,
                    'info' => null,
                    'data' => null
                ]);

            }

        });

        Queue::failing(function ($event) {

            $job = $this->getJobInstance($event);
            if($job instanceof ActionableJob) {

                Action::where('job_uuid', $event->job->uuid())->firstOrFail()->update([
                    'status' => ActionStatus::FAILED,
                    'info' => $event->exception->getMessage(),
                    'data' => str_replace(["\\", "'"], ["/", "\'"], json_encode([
                        'message' => $event->exception->getMessage(),
                        'line' => $event->exception->getLine(),
                        'file' => $event->exception->getFile(),
                    ]))
                ]);

            }

        });

    }

    private function getJobInstance($event)
    {
       return unserialize($event->job->payload()['data']['command']);
    }
}
