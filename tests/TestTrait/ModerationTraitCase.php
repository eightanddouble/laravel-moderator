<?php

namespace EightAndDouble\LaravelModerator\Tests\TestTrait;

use EightAndDouble\LaravelModerator\LaravelModeratorServiceProvider;
use EightAndDouble\LaravelModerator\Tests\Models\Post;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

class ModerationTraitCase extends Orchestra
{
    use DatabaseMigrations;

    protected $status_column;

    protected $moderated_at_column;

    protected function setUp(): void
    {
        parent::setUp();

        $this->status_column = 'status';
        $this->moderated_at_column = 'moderated_at';

        Post::$strictModeration = true;

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelModeratorServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        Schema::dropAllTables();
    }

    public function createPost($overrides = [], $amount = 1)
    {
        $posts = new \Illuminate\Database\Eloquent\Collection();
        for ($i = 0; $i < $amount; $i++) {
            $post = Post::create(array_merge(['moderated_at' => \Carbon\Carbon::now()], $overrides));
            $posts->push($post);
        }

        return (count($posts) > 1) ? $posts : $posts[0];
    }
}
