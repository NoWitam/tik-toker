<?php

use App\Jobs\CreateHistoricalTikTok;
use App\Models\Content;
use App\Models\Knowledge;
use App\Models\Tag;
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
    $content = Content::first();

    Storage::put($content::class . "_" . $content->id. "/scena1.png", file_get_contents("https://d14uq1pz7dzsdq.cloudfront.net/16894d13-cf01-42ae-8d06-4b8554d7b1dd_.png?Expires=1701624018&Signature=XSjWt-JazR-Nz2kwzxMW6AtC5x23j7l5YE~6~pEZATBY2NoLRTFLnwiX6If9wxr0-tDGeQWMr44ZTuqVyWeDVJsJAhOVWuKpZgVXGaiFcKcZhvyqDFrllhFwXVDg4jyFBhaPWvhITQ~pFWP1Vyu-Td1tBNnOGos~~PrvwwuZSe541xWP7494xYXdygHS~mHBTCdpaP1DwdygI5QPQbJV-vdKTwb9TAk-8bPDqZK6q4VMUqb5zK0GDx~LFTsHRfMwDrr3TfyCq9Wjtz9u8MyCkbv4pQKL9relGnIDfUTIk4VgD02dt7hEl5FZUe7qWIDLyxd87gj1wrzHsDIGQ6uj4Q__&Key-Pair-Id=K1F55BTI9AHGIK"));
});

Route::get('/', function () {



});
