# 権限・ルーティング・公開API設計

## 権限マトリクス

| 操作 | 未ログイン | ログイン利用者 | チャンネル作成者 | プライベートメンバー | 投稿者 |
|:--|:--:|:--:|:--:|:--:|:--:|
| 公開APIの読み取り | ○ | ○ | ○ | ○ | ○ |
| 登録・ログイン | ○ | — | — | — | — |
| 公開チャンネル閲覧・投稿 | — | ○ | ○ | ○ | ○ |
| プライベート閲覧・投稿 | — | — | ○ | ○ | — |
| チャンネル編集・削除 | — | — | ○ | — | — |
| 非公開メンバー管理 | — | — | ○ | — | — |
| 自分の投稿編集・削除 | — | — | — | — | ○ |
| 検索 | — | 閲覧可能な範囲 | 閲覧可能な範囲 | 閲覧可能な範囲 | 閲覧可能な範囲 |

プライベートチャンネルの非メンバーには、一覧・画面・検索を通じて存在を示さず、直接ID指定も見つからない応答にする。サーバー側で毎回認可する。

## 画面ルーティング

| Method | Path | 認証 | 処理 |
|:--|:--|:--|:--|
| GET/POST | /register | 不要 | 登録フォーム／登録 |
| GET/POST | /login | 不要 | ログインフォーム／認証 |
| POST | /logout | 必要 | ログアウト |
| GET | /channels | 必要 | 閲覧可能なチャンネル一覧 |
| GET/POST | /channels/create | 必要 | 作成フォーム／作成 |
| GET | /channels/{channel} | 必要・閲覧権限 | チャンネルと投稿 |
| GET/PUT/DELETE | /channels/{channel}/edit | 必要・作成者 | 編集画面／更新／削除 |
| GET | /channels/{channel}/members | 必要・作成者・非公開 | メンバー一覧 |
| POST/DELETE | /channels/{channel}/members/{user} | 必要・作成者・非公開 | 追加／作成者以外を削除 |
| POST | /channels/{channel}/messages | 必要・閲覧権限 | 投稿 |
| PUT/DELETE | /messages/{message} | 必要・投稿者 | 編集／論理削除 |
| GET | /messages/{message}/thread | 必要・閲覧権限 | スレッド表示 |
| POST | /messages/{message}/replies | 必要・閲覧権限 | 返信 |
| GET | /search?q=... | 必要 | 閲覧範囲内検索 |

## 公開API

認証不要、読み取り専用、JSON、ページングなし。APIのバージョンを `/api/v1` に含める。

| Method / Path | 応答 |
|:--|:--|
| GET `/api/v1/channels` | 公開チャンネルの `id`, `name`, `description`, `created_at` を配列で返す。 |
| GET `/api/v1/channels/{channel}/messages` | 公開チャンネルのトップレベル投稿と返信を古い順に返す。投稿者名、日時、本文、編集済み状態、親IDを含む。削除済み投稿は返さない。 |

指定IDが存在しない場合とプライベートの場合は同一の404 JSON応答とする。非公開チャンネル名・ID・存在を他のAPI応答に含めない。変更系APIは提供しない。
