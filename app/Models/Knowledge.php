<?php

namespace App\Models;

use App\Models\Traits\HasTags;
use App\Models\Interfaces\Taggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Knowledge extends Model implements Taggable
{
    use HasFactory, HasTags;
}
