<?php

namespace App\Jobs;

use App\Exceptions\ReleaseActionableJobException;
use App\Models\Content;
use App\Models\Enums\ContentStatus;
use App\Models\Interfaces\Actionable;
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Communication\Message;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class UploadVideo extends ActionableJob
{
    public $timeout = 300;
    /**
     * Create a new job instance.
     */
    public function __construct(
        public Content $content,
    ){}

    public function getActionable() : Actionable
    {
        return $this->content;
    }

    public function getType() : string
    {
        return 'Opublikuj wideo';
    }

    public function run() : array
    {
        $this->content->refresh();

        if($this->content->status != ContentStatus::CREATED) {
            throw new ReleaseActionableJobException(Carbon::now()->addMinute());
        }

        $browserFactory = new BrowserFactory(config('app.chromium'));
        $browser = $browserFactory->createBrowser([
          'headless' => true, // disable headless mode
          'sendSyncDefaultTimeout' => 30000,
          'connectionDelay' => 500
        ]);

        try {
            $page = $browser->createPage();

            $page->navigate('https://www.tiktok.com/')->waitForNavigation();
            $page->evaluate('document.cookie = "sessionid=' . $this->content->series->account->access_token . '; expires=Fri, 31 Dec 9999 23:59:59 GMT";');
            $page->navigate('https://www.tiktok.com/creator#/upload?scene=creator_center')->waitForNavigation();

            $page->waitUntilContainsElement('input');
            $input = $page->dom()->querySelector('input');
            $input->sendFile(Storage::path($this->getFolderName() . "/video.mp4"));

            $page->waitUntilContainsElement('.DraftEditor-root');

            $x = $page->callFunction("
                    function() {\n return document.querySelector('.DraftEditor-root').getBoundingClientRect().x;\n}"
                )->getReturnValue();
            $y = $page->callFunction("
                function() {\n return document.querySelector('.DraftEditor-root').getBoundingClientRect().y;\n}"
            )->getReturnValue();
            $page->mouse()->move($x, $y)->click();

            $keyboard = $page->keyboard()->press('Control')->type('a')->type('x')->release('Control');
            $keyboard->setKeyInterval(10)->typeText($this->content->script['title']);

            $keyboard->press("Shift");
            $page->getSession()->sendMessageSync(new Message('Input.dispatchKeyEvent', [
                'type' => 'rawKeyDown',
                'windowsVirtualKeyCode' => 13,
            ]));
            $keyboard->release("Shift");

            foreach($this->content->script['hashtags'] as $hashtag)
            {
                $page->evaluate("document.querySelector('.icon-style.hash').click();");
                $hashtag = mb_str_split(str_replace("#", "", $hashtag));
                foreach($hashtag as $key)
                {
                    $keyboard->type($key);
                }
                sleep(1);
                $page->getSession()->sendMessageSync(new Message('Input.dispatchKeyEvent', [
                    'type' => 'rawKeyDown',
                    'windowsVirtualKeyCode' => 13,
                ]));
            }

            $page->evaluate('document.querySelector(\'.btn-post button\').click()');

            $this->content->update([
                'publicated_at' => Carbon::now(),
                'status' => ContentStatus::PUBLISHED
            ]);

        } finally {
            $browser->close();
        }

        return [
            'info' => "Opublikowano o " . Carbon::now()->format('Y-m-d H:i:s')
        ];
    }

    private function getFolderName()
    {
        $className = explode("\\", Content::class);
        $className = array_pop($className);
        return "Models/{$className}/{$this->content->id}";
    }
}
