<?php

namespace App\Jobs;

use App\Exceptions\ReleaseActionableJobException;
use App\Models\Content;
use App\Models\Enums\ContentStatus;
use App\Models\Interfaces\Actionable;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CreateScenario extends ActionableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Content $content
    )
    {}

    public function getActionable() : Actionable
    {
        return $this->content;
    }

    public function getType() : string
    {
        return 'Utwórz scenariusz';
    }

    /**
     * Execute the job.
     */
    public function run(): array
    {
      if ($timestamp = Cache::get('limit-edenai')) {
        throw new ReleaseActionableJobException(Cache::get('limit-edenai'));
      }

      $response = $this->getScenario($this->content->knowledge);

      $file = str_replace(["```json", "```"], "", $response->generated_text);

      Storage::put($this->content::class . "_" . $this->content->id. "/scenariusz.json", $file);

      $this->content->status = ContentStatus::WAITING;
      $this->content->save();

      $this->action->increment('cost', $response->cost);

      return [
        'info' => 'Zakończono pomyślnie',
      ];
    }

    private function getScenario($knowledge)
    {
      try {
        $client = new \GuzzleHttp\Client();

        $response = $client->request('POST', 'https://api.edenai.run/v2/text/chat', [
          'headers' => [
            'accept' => 'application/json',
            'authorization' => 'Bearer ' . config("app.eden_ai_key"),
            'content-type' => 'application/json',
          ],
          'body' => json_encode([
            "response_as_dict" => true,
            "attributes_as_list" => false,
            "show_original_response" => false,
            "temperature" => 1,
            "max_tokens" => 4000,
            "providers" => "openai",
            "settings" => ["openai" => "gpt-4-1106-preview"],
            "text" => $knowledge->name . "\n" . $knowledge->content,
            "chatbot_global_action" => $this->content->series->instruction,
          ])
        ]);

        $data = json_decode($response->getBody()->__toString())->openai;

        return $data;

      } catch (ClientException $e) {
        if ($response->getStatusCode() == 429) {

          if ($response->hasHeader('Retry-After')) {

            $secondsRemaining = $response->getHeader('Retry-After')[0];

            $apiReadyAt = now()->addSeconds($secondsRemaining)->timestamp;
            Cache::put(
              'limit-edenai',
              $apiReadyAt,
              $secondsRemaining
            );
            throw new ReleaseActionableJobException($apiReadyAt);
            
          } else {
            throw $e;
          }

        }
      }
    }
}
