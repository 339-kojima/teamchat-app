<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChannelMemberTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_channel_owner_can_add_a_member_who_can_open_and_post_in_the_channel(): void
    {
        $owner = User::factory()->create(['name' => 'チャンネル作成者']);
        $member = User::factory()->create(['name' => '追加対象社員']);
        $channel = $this->createPrivateChannel($owner);

        $this->actingAs($owner)
            ->get(route('channels.members.index', ['channel' => $channel, 'q' => $member->email]))
            ->assertOk()
            ->assertSee($member->name)
            ->assertSee($member->email);

        $this->actingAs($owner)
            ->post(route('channels.members.store', $channel), [
                'user_id' => $member->id,
                'q' => $member->email,
            ])
            ->assertRedirect(route('channels.members.index', ['channel' => $channel, 'q' => $member->email]));

        $this->assertDatabaseHas('channel_user', [
            'channel_id' => $channel->id,
            'user_id' => $member->id,
        ]);

        $this->actingAs($member)
            ->get(route('channels.index'))
            ->assertOk()
            ->assertSee($channel->name);

        $this->actingAs($member)
            ->get(route('channels.show', $channel))
            ->assertOk()
            ->assertSee($channel->name);

        $this->actingAs($member)
            ->post(route('messages.store', $channel), ['body' => '追加後の投稿'])
            ->assertRedirect(route('channels.show', $channel));

        $this->assertDatabaseHas('messages', [
            'channel_id' => $channel->id,
            'user_id' => $member->id,
            'body' => '追加後の投稿',
        ]);
    }

    public function test_channel_owner_can_remove_a_member_while_remaining_a_member(): void
    {
        $owner = User::factory()->create(['name' => 'チャンネル作成者']);
        $member = User::factory()->create(['name' => '削除対象社員']);
        $channel = $this->createPrivateChannel($owner);
        $channel->members()->attach($member->id);

        $this->actingAs($owner)
            ->delete(route('channels.members.destroy', [
                'channel' => $channel,
                'user' => $member,
            ]))
            ->assertRedirect(route('channels.members.index', $channel));

        $this->assertDatabaseMissing('channel_user', [
            'channel_id' => $channel->id,
            'user_id' => $member->id,
        ]);
        $this->assertDatabaseHas('channel_user', [
            'channel_id' => $channel->id,
            'user_id' => $owner->id,
        ]);

        $this->actingAs($member)
            ->get(route('channels.index'))
            ->assertOk()
            ->assertDontSee($channel->name);

        $this->actingAs($member)
            ->get(route('channels.show', $channel))
            ->assertNotFound();
    }

    public function test_non_owner_cannot_view_or_change_channel_members(): void
    {
        $owner = User::factory()->create(['name' => 'チャンネル作成者']);
        $member = User::factory()->create(['name' => '現在のメンバー']);
        $otherUser = User::factory()->create(['name' => '対象外社員']);
        $channel = $this->createPrivateChannel($owner);
        $channel->members()->attach($member->id);

        $this->actingAs($member)
            ->get(route('channels.members.index', $channel))
            ->assertNotFound()
            ->assertDontSee($owner->name)
            ->assertDontSee($member->name);

        $this->actingAs($otherUser)
            ->post(route('channels.members.store', $channel), ['user_id' => $otherUser->id])
            ->assertNotFound();

        $this->actingAs($member)
            ->delete(route('channels.members.destroy', [
                'channel' => $channel,
                'user' => $member,
            ]))
            ->assertNotFound();

        $this->assertDatabaseHas('channel_user', [
            'channel_id' => $channel->id,
            'user_id' => $member->id,
        ]);
        $this->assertDatabaseMissing('channel_user', [
            'channel_id' => $channel->id,
            'user_id' => $otherUser->id,
        ]);
    }

    public function test_channel_owner_cannot_remove_themselves(): void
    {
        $owner = User::factory()->create();
        $channel = $this->createPrivateChannel($owner);

        $this->actingAs($owner)
            ->delete(route('channels.members.destroy', [
                'channel' => $channel,
                'user' => $owner,
            ]))
            ->assertNotFound();

        $this->assertDatabaseHas('channel_user', [
            'channel_id' => $channel->id,
            'user_id' => $owner->id,
        ]);
    }

    private function createPrivateChannel(User $owner): Channel
    {
        $channel = Channel::create([
            'owner_id' => $owner->id,
            'name' => 'プライベートチャンネル'.uniqid(),
            'description' => null,
            'is_private' => true,
        ]);

        $channel->members()->attach($owner->id);

        return $channel;
    }
}
