# チームチャットアプリ

## 何のアプリか

社員が話題ごとのチャンネルで会話し、過去のメッセージを検索できる社内向けチャットアプリです。公開チャンネルはログイン社員全員が利用でき、プライベートチャンネルは追加されたメンバーだけが利用できます。PCのブラウザで使うLaravelアプリです。

## できること

機能一覧は [`docs/design/features.md`](docs/design/features.md) を参照してください。

- アカウント登録、ログイン、ログアウト
- 公開・プライベートチャンネルの作成、閲覧、投稿
- 作成者によるチャンネルの編集・削除と、プライベートチャンネルのメンバー管理
- 自分のメッセージや返信の編集・削除、スレッドでの返信
- 閲覧できるチャンネル内のメッセージ検索
- 認証なしで利用できる公開チャンネル・メッセージの読み取りAPI
- デモ用の初期アカウント、チャンネル、メッセージ

## 動かす

GitとDocker Desktop（Docker Composeを含む）を用意してください。Dockerを起動してから、リポジトリをcloneします。

```bash
git clone <リポジトリのURL> teamchat-app
cd teamchat-app
```

Composer依存パッケージをインストールします。

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    -e COMPOSER_CACHE_DIR=/tmp/composer_cache \
    laravelsail/php82-composer:latest \
    composer install
```

環境設定を作り、コンテナを起動します。

```bash
cp .env.example .env
./vendor/bin/sail up -d
```

MySQLの起動を待ちます。次のコマンドで `mysql` が `Up (healthy)` になったら続けてください。

```bash
./vendor/bin/sail ps
```

アプリケーションキーを作り、テーブルと初期データを用意します。

```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
```

フロントエンド資産をインストールしてビルドします。

```bash
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

ブラウザで <http://localhost/login> を開くとログイン画面が表示されます。ポート80または3306を別のアプリが使用している場合は、そちらを停止してからSailを起動してください。DB接続エラーが出た場合は、MySQLが `healthy` になるまで待ってからコマンドを再実行してください。

## ログインする

初期データのアカウントは次の3つです。パスワードはいずれも `password` です。

| 表示名 | メールアドレス |
|:--|:--|
| 佐藤 太郎 | `sato@example.com` |
| 鈴木 花子 | `suzuki@example.com` |
| 高橋 健 | `takahashi@example.com` |

初期データのシーダーを再実行すると、初期データは所定の状態に戻ります。初期データ以外に追加した利用者データは削除されません。

## ひととおり触ってみる

1. 佐藤（`sato@example.com`）でログインし、チャンネル一覧から「開発」を開いてメッセージを読みます。
2. メッセージを投稿し、自分の投稿の編集や削除を試します。返信件数からスレッドを開いて返信もできます。
3. 「チャンネルを作る」から公開またはプライベートチャンネルを作ります。作成者には編集・削除の操作が表示されます。
4. プライベートチャンネルのメンバー管理で鈴木を追加します。鈴木（`suzuki@example.com`）でログインすると、そのチャンネルを開けます。
5. 「メッセージを検索」から本文の一部を検索します。結果から該当チャンネルやスレッドを開けます。

初期チャンネルは「全体連絡」「開発」「雑談」「採用プロジェクト」です。「採用プロジェクト」は佐藤と鈴木が利用できます。

## 公開API

公開APIはログイン不要の読み取り専用JSON APIです。ページングはありません。プライベートチャンネルの情報は返しません。

| メソッド | URL | 内容 |
|:--|:--|:--|
| `GET` | `/api/v1/channels` | 公開チャンネルの一覧 |
| `GET` | `/api/v1/channels/{channel}/messages` | 指定した公開チャンネルの未削除メッセージと返信 |

公開チャンネル一覧を取得します。

```bash
curl http://localhost/api/v1/channels
```

一覧で確認したチャンネルIDを指定して、メッセージを取得します。`2` は例なので、実際に返されたIDに置き換えてください。

```bash
curl http://localhost/api/v1/channels/2/messages
```

プライベートチャンネルと存在しないIDは、どちらも404になります。書き込み用APIはありません。

## テストと整形

```bash
./vendor/bin/sail artisan test
./vendor/bin/sail pint
```

テストはLaravelのFeatureテストとUnitテストを実行します。PintはPHPコードをLaravelの書式に整形します。

## 中身の地図

| パス | 内容 |
|:--|:--|
| `app/Http/Controllers/` | 画面・フォーム・公開APIの処理 |
| `app/Models/` | 利用者、チャンネル、メッセージのデータモデル |
| `app/Policies/` | チャンネルやメッセージの操作権限 |
| `routes/web.php`、`routes/api.php` | Web画面とAPIのルート |
| `resources/views/` | Bladeテンプレート |
| `database/migrations/`、`database/seeders/` | テーブル定義と初期データ |
| `tests/` | 自動テスト |
| `docs/spec.md` | 要求仕様 |
| `docs/design/` | 機能・画面・データ・権限・動作・受け入れ条件の設計書 |
| `mockup/` | 画面見本とデザインガイド |
