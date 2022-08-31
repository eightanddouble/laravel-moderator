<?php

use EightAndDouble\LaravelModerator\ModerationScope;
use EightAndDouble\LaravelModerator\Status;
use EightAndDouble\LaravelModerator\Tests\Models\Post;

it('returns_only_approved_stories', function () {
    $this->createPost([$this->status_column => Status::APPROVED], 5);
    $posts = Post::all();
    $this->assertNotEmpty($posts);
    foreach ($posts as $post) {
        $this->assertEquals(Status::APPROVED, $post->{$this->status_column});
    }
});

it('returns_only_rejected_stories', function () {
    $this->createPost([$this->status_column => Status::REJECTED], 5);
    $posts = (new Post())->newQueryWithoutScope(new ModerationScope())->rejected()->get();
    $this->assertNotEmpty($posts);
    foreach ($posts as $post) {
        $this->assertEquals(Status::REJECTED, $post->{$this->status_column});
    }
});

it('returns_only_pending_stories', function () {
    $this->createPost([$this->status_column => Status::PENDING], 5);
    $posts = (new Post())->newQueryWithoutScope(new ModerationScope())->pending()->get();
    $this->assertNotEmpty($posts);
    foreach ($posts as $post) {
        $this->assertEquals(Status::PENDING, $post->{$this->status_column});
    }
});

it('returns_stories_including_pending_ones', function () {
    $this->createPost([$this->status_column => Status::PENDING], 5);
    $posts = (new Post())->newQueryWithoutScope(new ModerationScope())->withPending()->get();
    $this->assertNotEmpty($posts);
    $this->assertTrue($posts > Post::all());
    foreach ($posts as $post) {
        $this->assertTrue(($post->{$this->status_column} == Status::APPROVED || $post->{$this->status_column} == Status::PENDING));
    }
});

it('returns_stories_including_rejected_ones', function () {
    $this->createPost([$this->status_column => Status::REJECTED], 5);
    $posts = (new Post())->newQueryWithoutScope(new ModerationScope())->withRejected()->get();
    $this->assertNotEmpty($posts);
    $this->assertTrue($posts > Post::all());
    foreach ($posts as $post) {
        $this->assertTrue(($post->{$this->status_column} == Status::APPROVED || $post->{$this->status_column} == Status::REJECTED));
    }
});

it('returns_stories_including_postponed_ones', function () {
    $this->createPost([$this->status_column => Status::POSTPONED], 5);
    $posts = (new Post())->newQueryWithoutScope(new ModerationScope())->withPostponed()->get();
    $this->assertNotEmpty($posts);
    $this->assertTrue($posts > Post::all());
    foreach ($posts as $post) {
        $this->assertTrue(($post->{$this->status_column} == Status::APPROVED || $post->{$this->status_column} == Status::POSTPONED));
    }
});

it('returns_all_stories', function () {
    $this->createPost([], 5);
    $posts = (new Post())->newQueryWithoutScope(new ModerationScope())->withAnyStatus()->get();
    $allStories = Post::all()
        ->merge(Post::pending()->get())
        ->merge(Post::rejected()->get());
    $this->assertNotEmpty($posts);
    $this->assertCount(count($posts), $allStories);
});

it('approves_stories', function () {
    $posts = $this->createPost([$this->status_column => Status::PENDING], 4);
    $postsIds = $posts->pluck('id')->all();
    (new Post())->newQueryWithoutScope(new ModerationScope())->whereIn('id', $postsIds)->approve();
    foreach ($postsIds as $postId) {
        $this->assertDatabaseHas('posts', ['id' => $postId, $this->status_column => Status::APPROVED]);
    }
});

it('rejects_stories', function () {
    $posts = $this->createPost([$this->status_column => Status::PENDING], 4);
    $postsIds = $posts->pluck('id')->all();
    (new Post())->newQueryWithoutScope(new ModerationScope())->whereIn('id', $postsIds)->reject();
    foreach ($postsIds as $postId) {
        $this->assertDatabaseHas('posts', ['id' => $postId, $this->status_column => Status::REJECTED]);
    }
});

it('postpones_stories', function () {
    $posts = $this->createPost([$this->status_column => Status::PENDING], 4);
    $postsIds = $posts->pluck('id')->all();
    (new Post())->newQueryWithoutScope(new ModerationScope())->whereIn('id', $postsIds)->postpone();
    foreach ($postsIds as $postId) {
        $this->assertDatabaseHas('posts', ['id' => $postId, $this->status_column => Status::POSTPONED]);
    }
});

it('approves_a_story_by_id', function () {
    $post = $this->createPost([$this->status_column => Status::PENDING]);
    (new Post())->newQueryWithoutScope(new ModerationScope())->approve($post->id);
    $this->assertDatabaseHas(
        'posts',
        [
            'id' => $post->id,
            $this->status_column => Status::APPROVED,
            $this->moderated_at_column => \Carbon\Carbon::now(),
        ]
    );
});

it('rejects_a_story_by_id', function () {
    $post = $this->createPost([$this->status_column => Status::PENDING]);
    (new Post())->newQueryWithoutScope(new ModerationScope())->reject($post->id);
    $this->assertDatabaseHas(
        'posts',
        [
            'id' => $post->id,
            $this->status_column => Status::REJECTED,
            $this->moderated_at_column => \Carbon\Carbon::now(),
        ]
    );
});

it('postpones_a_story_by_id', function () {
    $post = $this->createPost([$this->status_column => Status::PENDING]);
    (new Post())->newQueryWithoutScope(new ModerationScope())->postpone($post->id);
    $this->assertDatabaseHas(
        'posts',
        [
            'id' => $post->id,
            $this->status_column => Status::POSTPONED,
            $this->moderated_at_column => \Carbon\Carbon::now(),
        ]
    );
});

it('updates_moderated_by_column_on_status_update', function () {
    config()->set('moderator.moderated_by_column', 'moderated_by');
    $posts = $this->createPost([$this->status_column => Status::PENDING], 3);
    (new Post())->newQueryWithoutScope(new ModerationScope())->where('id', '=', $posts[0]->id)->postpone();
    (new Post())->newQueryWithoutScope(new ModerationScope())->where('id', '=', $posts[1]->id)->approve();
    (new Post())->newQueryWithoutScope(new ModerationScope())->where('id', '=', $posts[2]->id)->reject();
    foreach ($posts as $post) {
        $this->assertDatabaseHas(
            'posts',
            [
                'id' => $post->id,
                $this->moderated_by_column => \Auth::user()->id,
            ]
        );
    }
});

it('updates_moderated_by_column_on_status_update_by_id', function () {
    config()->set('moderator.moderated_by_column', 'moderated_by');
    $posts = $this->createPost([$this->status_column => Status::PENDING], 3);
    (new Post())->newQueryWithoutScope(new ModerationScope())->postpone($posts[0]->id);
    (new Post())->newQueryWithoutScope(new ModerationScope())->approve($posts[1]->id);
    (new Post())->newQueryWithoutScope(new ModerationScope())->reject($posts[2]->id);
    foreach ($posts as $post) {
        $this->assertDatabaseHas(
            'posts',
            [
                'id' => $post->id,
                $this->moderated_by_column => \Auth::user()->id,
            ]
        );
    }
});

it('returns_approved_and_pending_stories_when_not_in_strict_mode', function () {
    Post::$strictModeration = false;
    $this->createPost([$this->status_column => Status::PENDING], 4);
    $this->createPost([$this->status_column => Status::APPROVED], 2);
    $posts = Post::all();
    $pendingCount = count(Post::pending()->get());
    $this->assertTrue($posts->count() > $pendingCount);
    $this->assertNotEmpty($posts);
    foreach ($posts as $post) {
        $this->assertTrue(($post->{$this->status_column} == Status::APPROVED || $post->{$this->status_column} == Status::PENDING));
    }
});

it('queries_pending_stories_by_default_when_not_in_strict_mode', function () {
    Post::$strictModeration = false;
    $posts = $this->createPost([$this->status_column => Status::PENDING], 5);
    $postsIds = $posts->pluck('id')->all();
    $postsReturned = Post::whereIn('id', $postsIds)->get();
    $this->assertCount(5, $postsReturned);
    foreach ($posts as $post) {
        $this->assertTrue(($post->{$this->status_column} == Status::PENDING));
    }
});

it('queries_approved_stories_when_not_in_strict_mode', function () {
    $this->createPost([$this->status_column => Status::APPROVED], 5);
    $posts = (new Post())->newQueryWithoutScope(new ModerationScope())->approved()->get();
    $this->assertNotEmpty($posts);
    foreach ($posts as $post) {
        $this->assertEquals(Status::APPROVED, $post->{$this->status_column});
    }
});
