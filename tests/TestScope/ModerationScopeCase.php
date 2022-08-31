<?php

namespace EightAndDouble\LaravelModerator\Tests\TestScope;

use EightAndDouble\LaravelModerator\LaravelModeratorServiceProvider;
use EightAndDouble\LaravelModerator\Tests\Models\Post;
use EightAndDouble\LaravelModerator\Tests\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

class ModerationScopeCase extends Orchestra
{
    use DatabaseMigrations;

    protected $status_column;

    protected $moderated_at_column;

    protected $moderated_by_column;

    protected function setUp(): void
    {
        parent::setUp();

        $this->status_column = 'status';
        $this->moderated_at_column = 'moderated_at';
        $this->moderated_by_column = 'moderated_by';

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Post::$strictModeration = true;

        $this->actingAsUser();
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

    public function actingAsUser()
    {
        $user = User::create([
            'name' => 'tester',
            'email' => mt_rand(1, 9999).'tester@test.com',
            'password' => 'password',
        ]);

        return $this->actingAs($user);
    }
}
