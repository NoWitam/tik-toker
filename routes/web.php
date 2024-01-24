<?php

use App\Jobs\ContactVideos;
use App\Jobs\CreateDiarization;
use App\Jobs\CreateHistoricalTikTok;
use App\Jobs\CreateScript;
use App\Jobs\CreateVideo;
use App\Models\Action;
use App\Models\Content;
use App\Models\Enums\ActionStatus;
use App\Models\Enums\ContentStatus;
use App\Models\Knowledge;
use App\Models\Series;
use App\Models\Tag;
use App\Services\ActionService;
use App\Services\ContentService;
use App\Services\SeriesService;
use Facebook\WebDriver\Interactions\Internal\WebDriverContextClickAction;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use BorodinVasiliy\Stories;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/test', function () {
    //ContentService::createContentForNextWeek();
});

Route::get('/chromium', function() {
    dd(config('app.chromium'));
});

Route::get('/', function () {


});

class A {

    public function test1()
    {
        return static::class;
    }

    public function test2()
    {
        return self::class;
    }

}

class B extends A {

}
