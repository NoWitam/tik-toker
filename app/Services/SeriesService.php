<?php

namespace App\Services;

use App\Models\Knowledge;
use App\Models\Series;
use Illuminate\Contracts\Database\Eloquent\Builder;

class SeriesService
{

    static function getAvailableKnowledgeBuilder(Series $serie) : Builder
    {
        return Knowledge::whereHas('tags', function (Builder $q) use ($serie) {
            $q->whereIn('taggables.tag_id', $serie->tags->pluck('id'));
        });
    }
}
