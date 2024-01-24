<?php

namespace App\Jobs;

use App\Exceptions\ReleaseActionableJobException;
use App\Models\Action;
use App\Models\Enums\ActionStatus;
use App\Models\Interfaces\Actionable;
use App\Models\Interfaces\Statusable;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use ReflectionClass;
use Throwable;

abstract class ActionableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Action $action;

    abstract public function run() : array;

    abstract public function getActionable(): Actionable;

    abstract public function getType(): string;

    protected array $apiResponses = [];

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $this->action = Action::where('job_uuid', $this->job->uuid())->firstOrFail();

        try{
            if(property_exists($this, 'api')) {
                if ($timestamp = Cache::get("limit-{$this->api}")) {
                    dump("Not calling api {$timestamp->format('H:i:s')}");
                    throw new ReleaseActionableJobException(Cache::get("limit-{$this->api}"));
                }
            }

            $data = $this->run();
            $info = null;

            if(isset($data['info'])) {
                $info = $data['info'];
                unset($data['info']);
            }
    
            $this->action->update([
                'status' => ActionStatus::SUCCESS,
                'data' => json_encode($data),
                'info' => $info
            ]);

        } catch(ReleaseActionableJobException $e) {

            $params = [];
            foreach((new ReflectionClass(static::class))->getConstructor()->getParameters() as $param)
            {
                $params[$param->name] = $this->{$param->name};
            }

            static::class::dispatch(
                ...$params
            )->onQueue($this->queue)->delay($e->getTime())->setAction($this->action);

            $this->action->update([
                'status' => ActionStatus::WAITING,
                'info' => $e->getMessage(),
            ]);

            $this->action->decrement('attempts');

            $this->delete();
        } catch(Throwable $e) {
            if($this->getActionable() instanceof Statusable) {
                $this->getActionable()->update(
                    $this->getActionable()->getChangesOnError()
                );
            }

            throw $e;
        }
    }

    public function setAction(Action $action)
    {
        $this->action = $action;
    }

    public function failed(Throwable $exception): void
    {
        Log::error(self::class . " fail");
        Log::error("Exception - {$exception->getMessage()}");
        Log::error("Api responses - " . json_encode($this->apiResponses));
    }

    protected function callApi(string $method, string $url, array $params)
    {
        try {
            $client = new \GuzzleHttp\Client();

            $response = $client->request($method, $url, $params);
            $this->apiResponses[$url] = $response;

            return $response;
            
        } catch (ClientException $e) {
            if(property_exists($this, 'api')) {
                $response = $e->getResponse();
                if ($response->getStatusCode() == 429) {
                    
                  if ($response->hasHeader('Retry-After')) {
                    dump($response->getHeader('Retry-After'));
                    dump($response);
                    $secondsRemaining = $response->getHeader('Retry-After')[0];

                    dump("retry after {$secondsRemaining} s");
        
                    $apiReadyAt = now()->addSeconds($secondsRemaining);
                    Cache::put(
                      "limit-{$this->api}",
                      $apiReadyAt,
                      $secondsRemaining
                    );
                    throw new ReleaseActionableJobException($apiReadyAt);
                  } 
                }
            }

            throw $e;
        }
    }
}
