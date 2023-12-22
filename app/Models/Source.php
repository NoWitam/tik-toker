<?php

namespace App\Models;

use App\Models\Interfaces\Actionable;
use App\Models\Traits\HasActions;
use App\Models\Traits\HasTags;
use App\Models\Interfaces\Taggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Source extends Model implements Taggable, Actionable
{
    use HasFactory, HasTags, HasActions;

    protected $fillable = [
        'name',
        'url',
        'eval_knowledge_url',
        'eval_knowledge_name',
        'eval_knowledge_content',
    ];
}
