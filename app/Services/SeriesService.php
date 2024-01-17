<?php

namespace App\Services;

use App\Models\Knowledge;
use App\Models\Series;
use Illuminate\Contracts\Database\Eloquent\Builder;

class SeriesService
{

    static function getAvailableKnowledgeBuilder(Series $series) : Builder
    {
        return Knowledge::whereHas('tags', function (Builder $q) use ($series) {
            $q->whereIn('taggables.tag_id', $series->tags->pluck('id'));
        });
    }

}
