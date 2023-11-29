<?php

namespace App\Models;

use App\Jobs\CreateHistoricalTikTok;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasFactory;

    public function knowledge()
    {
        return $this->belongsTo(Knowledge::class);
    }

    protected static function booted(): void
    {
        static::creating(function (Content $content) {
            $content->status = 0;
        });

        static::created(function (Content $content) {
            CreateHistoricalTikTok::dispatch($content);
        });
    }
}
