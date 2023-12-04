<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use HeadlessChromium\BrowserFactory;
use Illuminate\Support\Facades\Storage;
use HeadlessChromium\Communication\Message;

class UploadTikTok extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:upload-tiktok';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload TikTok';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $title = "Super tytół";
        $hashtags = [
            "#hashtag1",
            "#smiech",
            "#śmiech",
            "#kupasiana"
        ];

        $browserFactory = new BrowserFactory(config('app.chromium'));
        $browser = $browserFactory->createBrowser([
          'headless' => true, // disable headless mode
          'sendSyncDefaultTimeout' => 20000,
          'connectionDelay' => 500
        ]);

        try {
            $page = $browser->createPage();

            $page->navigate('https://www.tiktok.com/')->waitForNavigation();
            $page->evaluate('document.cookie = "sessionid=1d42be7e7a421774d9e656bb2e242d89; expires=Fri, 31 Dec 9999 23:59:59 GMT";');
            $page->navigate('https://www.tiktok.com/creator#/upload?scene=creator_center')->waitForNavigation();

            $page->waitUntilContainsElement('input');
            $input = $page->dom()->querySelector('input');
            $input->sendFile(Storage::path('video.mp4'));
            sleep(10);

            $x = $page->callFunction("
                    function() {\n return document.querySelector('.DraftEditor-root').getBoundingClientRect().x;\n}"
                )->getReturnValue();
            $y = $page->callFunction("
                function() {\n return document.querySelector('.DraftEditor-root').getBoundingClientRect().y;\n}"
            )->getReturnValue();
            $page->mouse()->move($x, $y)->click();

            $keyboard = $page->keyboard()->press('Control')->type('a')->type('x')->release('Control');
            $keyboard->setKeyInterval(10)->typeText($title);

            $keyboard->press("Shift");
            $page->getSession()->sendMessageSync(new Message('Input.dispatchKeyEvent', [
                'type' => 'rawKeyDown',
                'windowsVirtualKeyCode' => 13,
            ]));
            $keyboard->release("Shift");

            foreach($hashtags as $hashtag)
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
            sleep(1);

        } finally {
            $browser->close();
        }
    }

    private function clickEnter($page)
    {

    }
}
