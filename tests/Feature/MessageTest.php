<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guest_cannot_open_a_channel_or_post_a_message(): void
    {
        $owner = User::factory()->create();
        $channel = $this->createChannel($owner);

        $this->get(route('channels.show', $channel))
            ->assertRedirect(route('login'));

        $this->post(route('messages.store', $channel), ['body' => '投稿本文'])
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('messages', 0);
    }

    public function test_member_can_post_and_messages_are_displayed_oldest_first(): void
    {
        $author = User::factory()->create(['name' => '投稿者']);
        $channel = $this->createChannel($author);

        Message::create([
            'channel_id' => $channel->id,
            'user_id' => $author->id,
            'body' => '先のメッセージ',
            'created_at' => now()->subMinute(),
        ]);

        $this->actingAs($author)
            ->post(route('messages.store', $channel), ['body' => '後のメッセージ'])
            ->assertRedirect(route('channels.show', $channel));

        $this->get(route('channels.show', $channel))
            ->assertOk()
            ->assertSee('先のメッセージ')
            ->assertSee('後のメッセージ')
            ->assertSee('投稿者')
            ->assertSeeInOrder(['先のメッセージ', '後のメッセージ']);

        $this->assertDatabaseHas('messages', [
            'channel_id' => $channel->id,
            'user_id' => $author->id,
            'parent_id' => null,
            'body' => '後のメッセージ',
        ]);
    }

    public function test_message_post_rejects_blank_and_overlong_body(): void
    {
        $author = User::factory()->create();
        $channel = $this->createChannel($author);

        $this->actingAs($author)
            ->from(route('channels.show', $channel))
            ->post(route('messages.store', $channel), ['body' => " \n "])
            ->assertSessionHasErrors([
                'body' => 'メッセージを入力してください',
            ]);

        $this->actingAs($author)
            ->from(route('channels.show', $channel))
            ->post(route('messages.store', $channel), ['body' => str_repeat('あ', 1001)])
            ->assertSessionHasErrors([
                'body' => '1,000文字以内で入力してください',
            ]);

        $this->assertDatabaseCount('messages', 0);
    }

    public function test_author_can_edit_own_message_and_edited_marker_is_shown(): void
    {
        $author = User::factory()->create();
        $channel = $this->createChannel($author);
        $message = $this->createMessage($channel, $author, '変更前');

        $this->actingAs($author)
            ->get(route('channels.show', ['channel' => $channel, 'edit' => $message->id]))
            ->assertOk()
            ->assertSee('変更前')
            ->assertSee('保存する');

        $this->actingAs($author)
            ->put(route('messages.update', $message), ['body' => '変更後'])
            ->assertRedirect(route('channels.show', $channel));

        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'body' => '変更後',
        ]);
        $this->assertNotNull($message->fresh()->edited_at);

        $this->get(route('channels.show', $channel))
            ->assertSee('変更後')
            ->assertSee('編集済み');
    }

    public function test_other_user_cannot_edit_or_delete_a_message(): void
    {
        $author = User::factory()->create();
        $otherUser = User::factory()->create();
        $channel = $this->createChannel($author);
        $message = $this->createMessage($channel, $author, '本人の投稿');
        $reply = $this->createMessage($channel, $author, '本人の返信', $message);

        $this->actingAs($otherUser)
            ->get(route('channels.show', ['channel' => $channel, 'edit' => $message->id]))
            ->assertNotFound();

        $this->actingAs($otherUser)
            ->get(route('messages.delete.confirm', $message))
            ->assertNotFound();

        $this->actingAs($otherUser)
            ->put(route('messages.update', $message), ['body' => '書き換え'])
            ->assertNotFound();

        $this->actingAs($otherUser)
            ->put(route('messages.update', $reply), ['body' => '返信の書き換え'])
            ->assertNotFound();

        $this->actingAs($otherUser)
            ->delete(route('messages.destroy', $message))
            ->assertNotFound();

        $this->actingAs($otherUser)
            ->delete(route('messages.destroy', $reply))
            ->assertNotFound();

        $this->assertSame('本人の投稿', $message->fresh()->body);
        $this->assertSame('本人の返信', $reply->fresh()->body);
        $this->assertNull($message->fresh()->deleted_at);
    }

    public function test_author_can_soft_delete_message_and_keep_author_time_and_replies(): void
    {
        $author = User::factory()->create();
        $replier = User::factory()->create();
        $channel = $this->createChannel($author);
        $message = $this->createMessage($channel, $author, '削除対象');
        $createdAt = $message->created_at;
        $reply = $this->createMessage($channel, $replier, '残る返信', $message);

        $this->actingAs($author)
            ->get(route('messages.delete.confirm', $message))
            ->assertOk()
            ->assertSee('削除対象');

        $this->actingAs($author)
            ->delete(route('messages.destroy', $message))
            ->assertRedirect(route('channels.show', $channel));

        $deletedMessage = $message->fresh();
        $this->assertNull($deletedMessage->body);
        $this->assertNotNull($deletedMessage->deleted_at);
        $this->assertSame($author->id, $deletedMessage->user_id);
        $this->assertSame($createdAt->toDateTimeString(), $deletedMessage->created_at->toDateTimeString());
        $this->assertDatabaseHas('messages', [
            'id' => $reply->id,
            'parent_id' => $message->id,
            'body' => '残る返信',
        ]);

        $this->get(route('channels.show', $channel))
            ->assertSee('このメッセージは削除されました')
            ->assertSee('返信1件');
    }

    public function test_author_can_edit_and_delete_a_reply_without_a_thread_screen(): void
    {
        $parentAuthor = User::factory()->create();
        $replyAuthor = User::factory()->create();
        $channel = $this->createChannel($parentAuthor);
        $parent = $this->createMessage($channel, $parentAuthor, '親投稿');
        $reply = $this->createMessage($channel, $replyAuthor, '返信前', $parent);

        $this->actingAs($replyAuthor)
            ->put(route('messages.update', $reply), ['body' => '返信後'])
            ->assertRedirect(route('channels.show', $channel));

        $this->assertDatabaseHas('messages', [
            'id' => $reply->id,
            'parent_id' => $parent->id,
            'body' => '返信後',
        ]);
        $this->assertNotNull($reply->fresh()->edited_at);

        $this->actingAs($replyAuthor)
            ->delete(route('messages.destroy', $reply))
            ->assertRedirect(route('channels.show', $channel));

        $this->assertDatabaseHas('messages', [
            'id' => $reply->id,
            'parent_id' => $parent->id,
            'body' => null,
        ]);
        $this->assertNotNull($reply->fresh()->deleted_at);
    }

    public function test_removed_private_channel_member_cannot_post_or_edit_own_message(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $channel = $this->createChannel($owner, true);
        $channel->members()->attach($member->id);
        $message = $this->createMessage($channel, $member, '以前の投稿');
        $channel->members()->detach($member->id);

        $this->actingAs($member)
            ->get(route('channels.show', ['channel' => $channel, 'edit' => $message->id]))
            ->assertNotFound();

        $this->actingAs($member)
            ->post(route('messages.store', $channel), ['body' => 'アクセス不可'])
            ->assertNotFound();

        $this->assertSame('以前の投稿', $message->fresh()->body);
        $this->assertDatabaseCount('messages', 1);
    }

    public function test_deleting_channel_physically_deletes_its_messages_and_replies(): void
    {
        $owner = User::factory()->create();
        $replyAuthor = User::factory()->create();
        $channel = $this->createChannel($owner);
        $parent = $this->createMessage($channel, $owner, '親投稿');
        $reply = $this->createMessage($channel, $replyAuthor, '返信', $parent);

        $this->actingAs($owner)
            ->delete(route('channels.destroy', $channel), [
                'confirmation_name' => $channel->name,
            ])
            ->assertRedirect(route('channels.index'));

        $this->assertDatabaseMissing('messages', ['id' => $parent->id]);
        $this->assertDatabaseMissing('messages', ['id' => $reply->id]);
    }

    private function createChannel(User $owner, bool $isPrivate = false): Channel
    {
        $channel = Channel::create([
            'owner_id' => $owner->id,
            'name' => 'テストチャンネル'.uniqid(),
            'description' => null,
            'is_private' => $isPrivate,
        ]);
        $channel->members()->attach($owner->id);

        return $channel;
    }

    private function createMessage(Channel $channel, User $author, string $body, ?Message $parent = null): Message
    {
        return Message::create([
            'channel_id' => $channel->id,
            'user_id' => $author->id,
            'parent_id' => $parent?->id,
            'body' => $body,
        ]);
    }
}
