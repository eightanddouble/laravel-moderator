<?php

namespace EightAndDouble\LaravelModerator\Tests\Models;

use EightAndDouble\LaravelModerator\Moderatable;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use Moderatable;

    protected $table = 'posts';

    public static $strictModeration = true;

    protected $fillable = ['moderated_at', 'status'];
}
