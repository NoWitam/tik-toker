<?php

use App\Models\Action;
use App\Models\Content;
use App\Models\Enums\ContentStatus;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;


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

Content::find(40)->update([
    'status' => ContentStatus::CREATED
]);


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
