<?php

namespace App\Jobs;

use App\Models\Content;
use App\Models\Enums\ContentStatus;
use App\Models\Interfaces\Actionable;
use Illuminate\Support\Facades\Cache;

class CreateScript extends ActionableJob
{

    public $timeout = 300;
    public $api = "edenai";

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Content $content
    ){
      $content->update([
        'status' => ContentStatus::IN_PROCESS
      ]);
    }

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
        Cache::forget("content_{$this->content->id}_completed_process");

        $response = $this->getScript($this->content->knowledge);
        $script = json_decode(str_replace(["```json", "```"], "", $response->generated_text), true);

        $this->content->update([
          'script' => $script,
          'name' => $script['title'],
          'status' => ContentStatus::WAITING
        ]);

        $this->action->increment('cost', $response->cost);

        return [
          'info' => 'Zakończono pomyślnie',
        ];
    }

    private function getScript($knowledge)
    {
      $response = $this->callApi('POST', 'https://api.edenai.run/v2/text/chat', [
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

      return json_decode($response->getBody()->__toString())->openai;
    }
}
