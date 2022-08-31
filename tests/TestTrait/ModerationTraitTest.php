<?php

use EightAndDouble\LaravelModerator\Status;
use EightAndDouble\LaravelModerator\Tests\Models\Post;

it('returns_only_rejected_stories', function () {
    $posts = $this->createPost([$this->status_column => Status::REJECTED], 5);
    Post::withAnyStatus()->get();
    $this->assertNotEmpty($posts);
    foreach ($posts as $post) {
        $this->assertEquals(Status::REJECTED, $post->{$this->status_column});
    }
});

it('returns_only_pending_stories', function () {
    $posts = $this->createPost([$this->status_column => Status::PENDING], 5);
    Post::withAnyStatus()->get();
    $this->assertNotEmpty($posts);
    foreach ($posts as $post) {
        $this->assertEquals(Status::PENDING, $post->{$this->status_column});
    }
});

it('returns_only_postponed_stories', function () {
    $posts = $this->createPost([$this->status_column => Status::POSTPONED], 5);
    Post::withAnyStatus()->get();
    $this->assertNotEmpty($posts);
    foreach ($posts as $post) {
        $this->assertEquals(Status::POSTPONED, $post->{$this->status_column});
    }
});

it('approves_a_story_by_id', function () {
    $post = $this->createPost([$this->status_column => Status::PENDING]);
    Post::approve($post->id);
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
    Post::reject($post->id);
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
    Post::postpone($post->id);
    $this->assertDatabaseHas(
        'posts',
        [
            'id' => $post->id,
            $this->status_column => Status::POSTPONED,
            $this->moderated_at_column => \Carbon\Carbon::now(),
        ]
    );
});

it('pendings_a_story_by_id', function () {
    $post = $this->createPost([$this->status_column => Status::PENDING]);
    Post::pending($post->id);
    $this->assertDatabaseHas(
        'posts',
        [
            'id' => $post->id,
            $this->status_column => Status::PENDING,
            $this->moderated_at_column => \Carbon\Carbon::now(),
        ]
    );
});

it('determines_if_story_is_approved', function () {
    $postApproved = $this->createPost([$this->status_column => Status::APPROVED]);
    $postPending = $this->createPost([$this->status_column => Status::PENDING]);
    $postRejected = $this->createPost([$this->status_column => Status::REJECTED]);
    $postPostponed = $this->createPost([$this->status_column => Status::POSTPONED]);

    $this->assertFalse($postPending->isApproved());
    $this->assertTrue($postApproved->isApproved());
    $this->assertFalse($postRejected->isApproved());
    $this->assertFalse($postPostponed->isApproved());
});

it('determines_if_story_is_rejected', function () {
    $postApproved = $this->createPost([$this->status_column => Status::APPROVED]);
    $postPending = $this->createPost([$this->status_column => Status::PENDING]);
    $postRejected = $this->createPost([$this->status_column => Status::REJECTED]);
    $postPostponed = $this->createPost([$this->status_column => Status::POSTPONED]);

    $this->assertFalse($postPending->isRejected());
    $this->assertFalse($postApproved->isRejected());
    $this->assertTrue($postRejected->isRejected());
    $this->assertFalse($postPostponed->isRejected());
});

it('determines_if_story_is_pending', function () {
    $postApproved = $this->createPost([$this->status_column => Status::APPROVED]);
    $postPending = $this->createPost([$this->status_column => Status::PENDING]);
    $postRejected = $this->createPost([$this->status_column => Status::REJECTED]);
    $postPostponed = $this->createPost([$this->status_column => Status::POSTPONED]);

    $this->assertTrue($postPending->isPending());
    $this->assertFalse($postApproved->isPending());
    $this->assertFalse($postRejected->isPending());
    $this->assertFalse($postPostponed->isPending());
});

it('determines_if_story_is_postponed', function () {
    $postApproved = $this->createPost([$this->status_column => Status::APPROVED]);
    $postPending = $this->createPost([$this->status_column => Status::PENDING]);
    $postRejected = $this->createPost([$this->status_column => Status::REJECTED]);
    $postPostponed = $this->createPost([$this->status_column => Status::POSTPONED]);

    $this->assertFalse($postPending->isPostponed());
    $this->assertFalse($postApproved->isPostponed());
    $this->assertFalse($postRejected->isPostponed());
    $this->assertTrue($postPostponed->isPostponed());
});

it('casts_moderated_at_attribute_as_a_date', function () {
    $post = $this->createPost();
    Post::approve($post->id);
    $post = Post::find($post->id);
    $this->assertInstanceOf(\Carbon\Carbon::class, $post->{$this->moderated_at_column});
});

it('deletes_rejected_resources', function () {
    $post = $this->createPost([$this->status_column => Status::REJECTED]);
    $postDel = Post::withRejected()->where('id', $post->id)->first();
    $postDel->delete();
    $this->assertDatabaseMissing('posts', ['id' => $post->id]);
});

it('deletes_resources_of_any_status', function () {
    $posts = $this->createPost([], 4);
    Post::approve($posts[0]->id);
    Post::reject($posts[1]->id);
    Post::postpone($posts[2]->id);
    foreach ($posts as $post) {
        $post->delete();
    }
    $this->assertDatabaseMissing('posts', ['id' => $posts[0]->id]);
    $this->assertDatabaseMissing('posts', ['id' => $posts[1]->id]);
    $this->assertDatabaseMissing('posts', ['id' => $posts[2]->id]);
});

it('marks_as_approved_an_instance', function () {
    $post = $this->createPost([$this->status_column => Status::PENDING]);
    $post->markApproved();
    $this->assertEquals(Status::APPROVED, $post->status);
    $this->assertDatabaseHas(
        'posts',
        ['id' => $post->id, $this->status_column => Status::APPROVED, $this->moderated_at_column => \Carbon\Carbon::now()]
    );
});

it('marks_as_rejected_an_instance', function () {
    $post = $this->createPost([$this->status_column => Status::PENDING]);
    $post->markRejected();
    $this->assertEquals(Status::REJECTED, $post->status);
    $this->assertDatabaseHas(
        'posts',
        ['id' => $post->id, $this->status_column => Status::REJECTED, $this->moderated_at_column => \Carbon\Carbon::now()]
    );
});

it('marks_as_postponed_an_instance', function () {
    $post = $this->createPost([$this->status_column => Status::PENDING]);
    $post->markPostponed();
    $this->assertEquals(Status::POSTPONED, $post->status);
    $this->assertDatabaseHas(
        'posts',
        ['id' => $post->id, $this->status_column => Status::POSTPONED, $this->moderated_at_column => \Carbon\Carbon::now()]
    );
});

it('marks_as_pending_an_instance', function () {
    $post = $this->createPost([$this->status_column => Status::PENDING]);
    $post->markPending();
    $this->assertEquals(Status::PENDING, $post->status);
    $this->assertDatabaseHas(
        'posts',
        ['id' => $post->id, $this->status_column => Status::PENDING, $this->moderated_at_column => \Carbon\Carbon::now()]
    );
});
