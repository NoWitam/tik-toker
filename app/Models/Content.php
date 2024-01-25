<?php

namespace App\Models;

use App\Jobs\CreateScript;
use App\Jobs\UploadVideo;
use App\Models\Enums\ContentStatus;
use App\Models\Interfaces\Actionable;
use App\Models\Traits\HasActions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Content extends Model implements Actionable
{
    use HasFactory, HasActions, SoftDeletes;

    protected $casts = [
        'status' => ContentStatus::class,
        'script' => 'array',
        'publication_time' => 'datetime',
        'publicated_at' => 'datetime'
    ];

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
            UploadVideo::dispatch($content)->onQueue('content_upload')->delay($content->publication_time);
        });
    }
}
