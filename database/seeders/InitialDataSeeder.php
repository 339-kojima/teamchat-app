<?php

namespace Database\Seeders;

use App\Models\Channel;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $users = $this->seedUsers();
            $channels = $this->seedChannels($users);

            $this->seedMessages($users, $channels);
        });
    }

    /**
     * @return array<string, User>
     */
    private function seedUsers(): array
    {
        $records = [
            'sato' => [
                'seed_key' => 'initial-user-sato',
                'name' => '佐藤 太郎',
                'email' => 'sato@example.com',
            ],
            'suzuki' => [
                'seed_key' => 'initial-user-suzuki',
                'name' => '鈴木 花子',
                'email' => 'suzuki@example.com',
            ],
            'takahashi' => [
                'seed_key' => 'initial-user-takahashi',
                'name' => '高橋 健',
                'email' => 'takahashi@example.com',
            ],
        ];

        $users = [];

        foreach ($records as $key => $record) {
            $user = User::query()->where('seed_key', $record['seed_key'])->first();

            if ($user === null) {
                if (User::query()->where('email', $record['email'])->exists()) {
                    throw new RuntimeException("初期アカウントのメールアドレスが既存データと重複しています: {$record['email']}");
                }

                $user = new User;
            }

            $user->forceFill([
                'seed_key' => $record['seed_key'],
                'name' => $record['name'],
                'email' => $record['email'],
                'password' => 'password',
            ])->save();

            $users[$key] = $user;
        }

        return $users;
    }

    /**
     * @param  array<string, User>  $users
     * @return array<string, Channel>
     */
    private function seedChannels(array $users): array
    {
        $records = [
            'general' => [
                'seed_key' => 'initial-channel-general',
                'name' => '全体連絡',
                'description' => '全社へのお知らせや共有事項',
                'is_private' => false,
                'owner' => 'suzuki',
                'members' => ['suzuki'],
            ],
            'development' => [
                'seed_key' => 'initial-channel-development',
                'name' => '開発',
                'description' => '開発に関する相談と報告',
                'is_private' => false,
                'owner' => 'sato',
                'members' => ['sato'],
            ],
            'chat' => [
                'seed_key' => 'initial-channel-chat',
                'name' => '雑談',
                'description' => '仕事の合間のちょっとした話題',
                'is_private' => false,
                'owner' => 'takahashi',
                'members' => ['takahashi'],
            ],
            'recruitment' => [
                'seed_key' => 'initial-channel-recruitment',
                'name' => '採用プロジェクト',
                'description' => '採用活動に関する相談と進捗共有',
                'is_private' => true,
                'owner' => 'sato',
                'members' => ['sato', 'suzuki'],
            ],
        ];

        $channels = [];

        foreach ($records as $key => $record) {
            $channel = Channel::query()->where('seed_key', $record['seed_key'])->first();

            if ($channel === null) {
                $nameIsTaken = Channel::query()
                    ->where('name', $record['name'])
                    ->where('is_private', $record['is_private'])
                    ->exists();

                if ($nameIsTaken) {
                    throw new RuntimeException("初期チャンネル名が既存データと重複しています: {$record['name']}");
                }

                $channel = new Channel;
            }

            $channel->forceFill([
                'seed_key' => $record['seed_key'],
                'owner_id' => $users[$record['owner']]->getKey(),
                'name' => $record['name'],
                'description' => $record['description'],
                'is_private' => $record['is_private'],
            ])->save();

            $channel->members()->syncWithoutDetaching(array_map(
                fn (string $member): int => $users[$member]->getKey(),
                $record['members']
            ));

            $channels[$key] = $channel;
        }

        return $channels;
    }

    /**
     * @param  array<string, User>  $users
     * @param  array<string, Channel>  $channels
     */
    private function seedMessages(array $users, array $channels): void
    {
        $records = [
            ['key' => 'general-01', 'channel' => 'general', 'author' => 'suzuki', 'body' => 'おはようございます。今週もよろしくお願いします。全社ミーティングは金曜日の15時からです。', 'created_at' => '2026-11-16 09:05:00'],
            ['key' => 'general-02', 'channel' => 'general', 'author' => 'sato', 'body' => '金曜のミーティングで使う進捗資料、木曜のお昼までに共有してもらえると助かります。', 'created_at' => '2026-11-16 10:12:00'],
            ['key' => 'general-03', 'channel' => 'general', 'author' => 'takahashi', 'body' => '会議室は3階です。開始時刻を15時半と案内していましたが、正しくは15時です。', 'created_at' => '2026-11-16 13:40:00', 'edited_at' => '2026-11-16 13:48:00'],
            ['key' => 'general-04', 'channel' => 'general', 'author' => 'suzuki', 'body' => '来客用の入館証が受付に届いています。必要な方は声をかけてください。', 'created_at' => '2026-11-17 09:20:00'],
            ['key' => 'general-05', 'channel' => 'general', 'author' => 'sato', 'body' => '今月の避難訓練は来週火曜日の午前10時に行う予定です。', 'created_at' => '2026-11-17 11:05:00'],
            ['key' => 'general-06', 'channel' => 'general', 'author' => 'takahashi', 'body' => '共有スペースのモニターを交換しました。会議で使えます。', 'created_at' => '2026-11-18 09:35:00'],
            ['key' => 'general-07', 'channel' => 'general', 'author' => 'suzuki', 'body' => '健康診断の希望日時をフォームに入力してください。締め切りは今週金曜です。', 'created_at' => '2026-11-18 14:10:00'],
            ['key' => 'general-08', 'channel' => 'general', 'author' => 'sato', 'body' => null, 'created_at' => '2026-11-19 09:00:00', 'deleted_at' => '2026-11-19 09:30:00'],
            ['key' => 'general-09', 'channel' => 'general', 'author' => 'takahashi', 'body' => '今日の郵便物は総務の棚に置いてあります。', 'created_at' => '2026-11-19 13:25:00'],
            ['key' => 'general-10', 'channel' => 'general', 'author' => 'suzuki', 'body' => '年末の休業予定を更新しました。確認をお願いします。', 'created_at' => '2026-11-20 09:10:00'],
            ['key' => 'general-02-reply-01', 'channel' => 'general', 'author' => 'suzuki', 'parent' => 'general-02', 'body' => '承知しました。木曜午前中に共有します。', 'created_at' => '2026-11-16 10:24:00'],
            ['key' => 'general-02-reply-02', 'channel' => 'general', 'author' => 'takahashi', 'parent' => 'general-02', 'body' => '私の担当分も同じ資料に追記しておきます。', 'created_at' => '2026-11-16 10:31:00'],
            ['key' => 'general-02-reply-03', 'channel' => 'general', 'author' => 'sato', 'parent' => 'general-02', 'body' => 'ありがとうございます。確認して会議前にまとめます。', 'created_at' => '2026-11-16 10:45:00'],
            ['key' => 'general-05-reply-01', 'channel' => 'general', 'author' => 'suzuki', 'parent' => 'general-05', 'body' => '参加が難しい方は、前日までに連絡してください。', 'created_at' => '2026-11-17 11:22:00'],

            ['key' => 'development-01', 'channel' => 'development', 'author' => 'sato', 'body' => '認証画面のレビューを反映しました。入力エラーの表示も確認済みです。', 'created_at' => '2026-11-16 09:18:00'],
            ['key' => 'development-02', 'channel' => 'development', 'author' => 'takahashi', 'body' => '検索結果の並び順について相談です。投稿日時の古い順でよさそうでしょうか。', 'created_at' => '2026-11-16 11:05:00'],
            ['key' => 'development-03', 'channel' => 'development', 'author' => 'suzuki', 'body' => 'APIのレスポンス例を更新しました。削除済み親の返信も確認できます。', 'created_at' => '2026-11-16 15:30:00'],
            ['key' => 'development-04', 'channel' => 'development', 'author' => 'sato', 'body' => 'チャンネル一覧の権限チェックをポリシーにまとめています。', 'created_at' => '2026-11-17 09:42:00'],
            ['key' => 'development-05', 'channel' => 'development', 'author' => 'takahashi', 'body' => 'ローカル環境でマイグレーションを実行できました。次に初期データを確認します。', 'created_at' => '2026-11-17 13:12:00'],
            ['key' => 'development-06', 'channel' => 'development', 'author' => 'suzuki', 'body' => '返信件数は削除済みの返信も含めて数える仕様で進めています。', 'created_at' => '2026-11-18 10:08:00'],
            ['key' => 'development-07', 'channel' => 'development', 'author' => 'sato', 'body' => 'チャンネル削除時の外部キー連鎖を確認しました。', 'created_at' => '2026-11-18 13:55:00'],
            ['key' => 'development-08', 'channel' => 'development', 'author' => 'takahashi', 'body' => '検索で返信本文も結果に含まれるようになりました。画面遷移を見てもらえますか。', 'created_at' => '2026-11-19 09:25:00'],
            ['key' => 'development-09', 'channel' => 'development', 'author' => 'suzuki', 'body' => 'Pintを実行して差分がないことを確認しました。', 'created_at' => '2026-11-19 14:40:00'],
            ['key' => 'development-10', 'channel' => 'development', 'author' => 'sato', 'body' => '受け入れ確認用の画面操作を始めます。気づいた点はここに共有します。', 'created_at' => '2026-11-20 09:35:00'],
            ['key' => 'development-02-reply-01', 'channel' => 'development', 'author' => 'suzuki', 'parent' => 'development-02', 'body' => 'はい、検索結果から開いた先でも時系列が分かりやすいと思います。', 'created_at' => '2026-11-16 11:24:00'],
            ['key' => 'development-02-reply-02', 'channel' => 'development', 'author' => 'sato', 'parent' => 'development-02', 'body' => '賛成です。APIも同じ並びに揃えましょう。', 'created_at' => '2026-11-16 11:37:00'],
            ['key' => 'development-08-reply-01', 'channel' => 'development', 'author' => 'suzuki', 'parent' => 'development-08', 'body' => '今から確認します。返信の投稿者と時刻も表示されていました。', 'created_at' => '2026-11-19 09:44:00'],

            ['key' => 'chat-01', 'channel' => 'chat', 'author' => 'takahashi', 'body' => '駅前に新しいパン屋さんができていました。クロワッサンがおすすめです。', 'created_at' => '2026-11-16 08:55:00'],
            ['key' => 'chat-02', 'channel' => 'chat', 'author' => 'suzuki', 'body' => 'お昼に近くの公園を散歩したら、紅葉がきれいでした。', 'created_at' => '2026-11-16 12:10:00'],
            ['key' => 'chat-03', 'channel' => 'chat', 'author' => 'sato', 'body' => '週末に見た映画が面白かったです。最近のおすすめがあれば知りたいです。', 'created_at' => '2026-11-16 16:20:00'],
            ['key' => 'chat-04', 'channel' => 'chat', 'author' => 'takahashi', 'body' => '朝晩冷えるようになりましたね。みなさん暖かくして過ごしてください。', 'created_at' => '2026-11-17 09:15:00'],
            ['key' => 'chat-05', 'channel' => 'chat', 'author' => 'suzuki', 'body' => 'コーヒー豆を変えたら香りがよくなりました。休憩時間にどうぞ。', 'created_at' => '2026-11-17 14:05:00'],
            ['key' => 'chat-06', 'channel' => 'chat', 'author' => 'sato', 'body' => '近所で開催されるマルシェに行ってみようと思っています。', 'created_at' => '2026-11-18 10:30:00'],
            ['key' => 'chat-07', 'channel' => 'chat', 'author' => 'takahashi', 'body' => '先週教えてもらったレシピを作りました。簡単でおいしかったです。', 'created_at' => '2026-11-18 15:45:00'],
            ['key' => 'chat-08', 'channel' => 'chat', 'author' => 'suzuki', 'body' => '会社の観葉植物に新しい葉が出ていました。少しずつ成長しています。', 'created_at' => '2026-11-19 09:50:00'],
            ['key' => 'chat-09', 'channel' => 'chat', 'author' => 'sato', 'body' => '雨の日が続きそうなので、折りたたみ傘を持ってきました。', 'created_at' => '2026-11-19 12:35:00'],
            ['key' => 'chat-10', 'channel' => 'chat', 'author' => 'takahashi', 'body' => '今週もお疲れさまでした。よい週末をお過ごしください。', 'created_at' => '2026-11-20 16:00:00'],
            ['key' => 'chat-02-reply-01', 'channel' => 'chat', 'author' => 'takahashi', 'parent' => 'chat-02', 'body' => 'この時期は夕方の景色もいいですよ。', 'created_at' => '2026-11-16 12:24:00'],
            ['key' => 'chat-06-reply-01', 'channel' => 'chat', 'author' => 'suzuki', 'parent' => 'chat-06', 'body' => 'いいですね。開催日はいつですか？', 'created_at' => '2026-11-18 10:42:00'],
            ['key' => 'chat-06-reply-02', 'channel' => 'chat', 'author' => 'takahashi', 'parent' => 'chat-06', 'body' => '土曜日の午前中だそうです。食べ物のお店も出るみたいです。', 'created_at' => '2026-11-18 10:50:00'],

            ['key' => 'recruitment-01', 'channel' => 'recruitment', 'author' => 'sato', 'body' => '次回の面接候補日は11月24日と25日です。面接官の予定を確認しています。', 'created_at' => '2026-11-17 10:15:00'],
            ['key' => 'recruitment-02', 'channel' => 'recruitment', 'author' => 'suzuki', 'body' => '候補者向けの会社紹介資料を更新しました。開発チームの説明を追加しています。', 'created_at' => '2026-11-17 15:20:00'],
            ['key' => 'recruitment-03', 'channel' => 'recruitment', 'author' => 'sato', 'body' => '書類選考の結果をまとめました。明日の打ち合わせで次の選考について相談しましょう。', 'created_at' => '2026-11-18 11:30:00'],
            ['key' => 'recruitment-04', 'channel' => 'recruitment', 'author' => 'suzuki', 'body' => '面接後のアンケート案を作りました。確認をお願いします。', 'created_at' => '2026-11-19 13:10:00'],
            ['key' => 'recruitment-02-reply-01', 'channel' => 'recruitment', 'author' => 'sato', 'parent' => 'recruitment-02', 'body' => 'ありがとうございます。仕事内容の説明も分かりやすくなっています。', 'created_at' => '2026-11-17 15:38:00'],
        ];

        $messages = [];

        foreach ($records as $record) {
            $parent = $record['parent'] ?? null;
            $createdAt = $record['created_at'];
            $editedAt = $record['edited_at'] ?? null;
            $deletedAt = $record['deleted_at'] ?? null;

            $message = Message::query()->where('seed_key', 'initial-message-'.$record['key'])->first();
            $message ??= new Message;

            $message->forceFill([
                'seed_key' => 'initial-message-'.$record['key'],
                'channel_id' => $channels[$record['channel']]->getKey(),
                'user_id' => $users[$record['author']]->getKey(),
                'parent_id' => $parent === null ? null : $messages[$parent]->getKey(),
                'body' => $record['body'],
                'edited_at' => $editedAt,
                'deleted_at' => $deletedAt,
                'created_at' => $createdAt,
                'updated_at' => $deletedAt ?? $editedAt ?? $createdAt,
            ])->save();

            $messages[$record['key']] = $message;
        }
    }
}
