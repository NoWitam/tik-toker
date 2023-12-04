<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'eval_knowledge_url',
        'eval_knowledge_name',
        'eval_knowledge_content',
    ];

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

}
