<?php

namespace App\Models;

use App\Jobs\CreateScript;
use App\Models\Enums\ContentStatus;
use App\Models\Interfaces\Actionable;
use App\Models\Interfaces\Statusable;
use App\Models\Traits\HasActions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Content extends Model implements Actionable, Statusable
{
    use HasFactory, HasActions;

    protected $casts = [
        'status' => ContentStatus::class,
        'script' => 'array'
    ];

    public function getChangesOnError() : array
    {
        return [
            'status' => ContentStatus::ERROR
        ];
    }

    public function getArchivalScriptAttribute()
    {
        $className = explode("\\", self::class);
        $className = array_pop($className);
        return json_decode(Storage::get("Models/{$className}/{$this->getKey()}/archivalScript.json"), true);
    }

    public function setArchivalScriptAttribute(string|array $data)
    {
        if(is_array($data)) {
            $data = json_encode($data);
        }

        $className = explode("\\", self::class);
        $className = array_pop($className);
        Storage::put("Models/{$className}/{$this->getKey()}/archivalScript.json", $data);
    }

    public function knowledge()
    {
        return $this->belongsTo(Knowledge::class);
    }

    public function series()
    {
        return $this->belongsTo(Series::class);
    }

    protected static function booted(): void
    {
        static::created(function (Content $content) {
            CreateScript::dispatch($content)->onQueue(auth()->check() ? 'user' : 'content_create');
        });
    }
}
