<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PublicApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_channel_list_is_public_json_and_excludes_private_channels(): void
    {
        $owner = User::factory()->create();
        $publicChannel = $this->createChannel($owner, '公開チャンネル');
        $this->createChannel($owner, '非公開チャンネル', true);

        $this->assertGuest();
        $this->get('/api/v1/channels')
            ->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertExactJson([
                'data' => [
                    [
                        'id' => $publicChannel->id,
                        'name' => '公開チャンネル',
                        'description' => null,
                    ],
                ],
            ]);
    }

    public function test_message_list_is_oldest_first_and_keeps_replies_to_deleted_parents(): void
    {
        $owner = User::factory()->create(['name' => '投稿者']);
        $channel = $this->createChannel($owner, '公開チャンネル');
        $deletedParent = $this->createMessage(
            $channel,
            $owner,
            null,
            Carbon::parse('2026-11-18 09:00:00', 'Asia/Tokyo'),
            deletedAt: Carbon::parse('2026-11-18 09:30:00', 'Asia/Tokyo'),
        );
        $olderMessage = $this->createMessage(
            $channel,
            $owner,
            '古い投稿',
            Carbon::parse('2026-11-18 10:00:00', 'Asia/Tokyo'),
        );
        $reply = $this->createMessage(
            $channel,
            $owner,
            '削除済み親への返信',
            Carbon::parse('2026-11-18 11:00:00', 'Asia/Tokyo'),
            parent: $deletedParent,
        );
        $newerMessage = $this->createMessage(
            $channel,
            $owner,
            '新しい投稿',
            Carbon::parse('2026-11-18 12:00:00', 'Asia/Tokyo'),
        );

        $this->assertGuest();
        $this->getJson('/api/v1/channels/'.$channel->id.'/messages')
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    [
                        'id' => $olderMessage->id,
                        'parent_id' => null,
                        'author' => '投稿者',
                        'body' => '古い投稿',
                        'created_at' => '2026-11-18T10:00:00+09:00',
                        'edited_at' => null,
                    ],
                    [
                        'id' => $reply->id,
                        'parent_id' => $deletedParent->id,
                        'author' => '投稿者',
                        'body' => '削除済み親への返信',
                        'created_at' => '2026-11-18T11:00:00+09:00',
                        'edited_at' => null,
                    ],
                    [
                        'id' => $newerMessage->id,
                        'parent_id' => null,
                        'author' => '投稿者',
                        'body' => '新しい投稿',
                        'created_at' => '2026-11-18T12:00:00+09:00',
                        'edited_at' => null,
                    ],
                ],
            ]);
    }

    public function test_private_and_missing_channels_return_the_same_not_found_json(): void
    {
        $owner = User::factory()->create();
        $privateChannel = $this->createChannel($owner, '非公開チャンネル', true);

        $privateResponse = $this->getJson('/api/v1/channels/'.$privateChannel->id.'/messages');
        $missingResponse = $this->getJson('/api/v1/channels/999999/messages');

        $expected = [
            'error' => [
                'code' => 'not_found',
                'message' => 'チャンネルが見つかりません。',
            ],
        ];

        $privateResponse->assertNotFound()->assertExactJson($expected);
        $missingResponse->assertNotFound()->assertExactJson($expected);
        $this->assertSame($privateResponse->getContent(), $missingResponse->getContent());
    }

    public function test_write_methods_are_rejected_without_changing_api_data(): void
    {
        $owner = User::factory()->create();
        $channel = $this->createChannel($owner, '公開チャンネル');
        $message = $channel->messages()->create([
            'user_id' => $owner->id,
            'body' => '変更されない投稿',
        ]);

        foreach (['POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'] as $method) {
            foreach ([
                '/api/v1/channels',
                '/api/v1/channels/'.$channel->id.'/messages',
            ] as $uri) {
                $this->json($method, $uri, [
                    'name' => 'API経由の追加',
                    'body' => 'API経由の変更',
                ])
                    ->assertStatus(405)
                    ->assertExactJson([
                        'error' => [
                            'code' => 'method_not_allowed',
                            'message' => 'このAPIは読み取り専用です。',
                        ],
                    ]);
            }
        }

        $this->assertDatabaseCount('channels', 1);
        $this->assertDatabaseCount('messages', 1);
        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'body' => '変更されない投稿',
        ]);
    }

    private function createChannel(User $owner, string $name, bool $isPrivate = false): Channel
    {
        return Channel::create([
            'owner_id' => $owner->id,
            'name' => $name,
            'description' => null,
            'is_private' => $isPrivate,
        ]);
    }

    private function createMessage(
        Channel $channel,
        User $author,
        ?string $body,
        Carbon $createdAt,
        ?Message $parent = null,
        ?Carbon $deletedAt = null,
    ): Message {
        $message = $channel->messages()->make([
            'user_id' => $author->id,
            'body' => $body,
            'deleted_at' => $deletedAt,
        ]);
        $message->created_at = $createdAt;
        $message->parent()->associate($parent);
        $message->save();

        return $message;
    }
}
