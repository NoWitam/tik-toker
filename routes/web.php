<?php

use App\Jobs\CreateHistoricalTikTok;
use App\Models\Action;
use App\Models\Content;
use App\Models\Knowledge;
use App\Models\Series;
use App\Models\Tag;
use App\Services\SeriesService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use BorodinVasiliy\Stories;
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
    $series = Series::find(1);
   dump(SeriesService::getAvailableKnowledgeBuilder($series)->count());
});

Route::get('/chromium', function() {
    dd(config('app.chromium'));
});

Route::get('/', function () {


});
