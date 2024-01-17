<?php

namespace App\Models;

use App\Jobs\CreateHistoricalTikTok;
use App\Jobs\CreateScenario;
use App\Models\Enums\ContentStatus;
use App\Models\Interfaces\Actionable;
use App\Models\Traits\HasActions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model implements Actionable
{
    use HasFactory, HasActions;

    protected $casts = [
        'status' => ContentStatus::class,
    ];

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
            CreateScenario::dispatch($content)->onQueue('content_create');
        });
    }
}
