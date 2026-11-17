# 振る舞い設計

## メッセージの状態遷移

```mermaid
stateDiagram-v2
  state "未編集" as Unedited
  state "編集済み" as Edited
  state "削除済み" as Deleted
  [*] --> Unedited: 投稿
  Unedited --> Edited: 本文を更新・edited_atを設定
  Edited --> Edited: 再編集・edited_atを更新
  Unedited --> Deleted: bodyをNULL・deleted_atを設定
  Edited --> Deleted: bodyをNULL・deleted_atを設定
```

`未編集` は `edited_at` と `deleted_at` が未設定、`編集済み` は `edited_at` のみ設定、`削除済み` は `deleted_at` 設定済みで `body` が `NULL` の状態。削除済み行は残し、投稿者・投稿時刻・親子関係を保持する。画面には本文の代わりに「このメッセージは削除されました」と表示する。削除済みメッセージの編集状態は、削除前の `edited_at` 値として保持する。

## シーケンス：投稿者がメッセージを削除

```mermaid
sequenceDiagram
  actor A as メッセージ投稿者
  participant B as ブラウザ
  participant W as Webアプリ
  participant DB as データベース

  A->>B: チャンネル画面で自分のメッセージの削除を選ぶ
  B->>W: GET /messages/{message}/delete
  W-->>B: 削除確認画面
  A->>B: 削除を確定する
  B->>W: DELETE /messages/{message}
  W->>DB: 対象メッセージと投稿者IDを取得
  DB-->>W: メッセージ情報
  W->>W: 投稿者本人かつチャンネル閲覧権限を確認
  alt 投稿者本人かつチャンネル閲覧権限あり
    W->>DB: bodyをNULL、deleted_atを設定
    Note over DB: 行ID・投稿者・created_at・返信は維持
    DB-->>W: 更新完了
    W->>DB: チャンネルのメッセージを再取得
    DB-->>W: 削除済み表示と返信件数
    W-->>B: チャンネル画面を再表示
  else 投稿者本人ではない、または対象が閲覧不可
    W-->>B: 404（変更なし）
  end
```

## 例外方針

- 入力値の空欄・上限超過・形式エラーは `screens.md` の項目別メッセージで画面に返す。サーバー側でも検証し、入力値とエラー項目を画面に戻す。
- 未認証の画面アクセスはログインへ誘導する。非メンバーのプライベートチャンネルと、存在しないチャンネルは同じ404として扱い、チャンネル名や内容を漏らさない。
- 作成者以外のチャンネル編集・削除、投稿者以外またはチャンネル閲覧不可の利用者によるメッセージ編集・削除は変更せず404とする。操作権限は各リクエストでポリシーにより確認する。
- 同じ公開範囲に同名チャンネルがある場合は作成・更新を行わず、チャンネル名欄にエラーを返す。
- メッセージの論理削除では `body = NULL`、`deleted_at` を設定し、メッセージ行と返信を保持する。検索・公開APIでは削除済み行を除外する。
- チャンネル削除ではチャンネル、メンバー情報、メッセージ・返信を外部キー連鎖で削除する。投稿者によるメッセージ削除は論理削除であり、返信を保持する。
- 公開APIの存在しない／非公開チャンネル応答は同一の404 JSON。その他の予期しないAPIエラーは詳細を含めない500 JSONを返す。Web画面の予期しないエラーも内部情報を表示しない。
- 削除済み親に付いた未削除返信は、公開APIで返信だけを返し、`parent_id` は削除済み親のIDのままにする（Q-19）。画面上のスレッドでは親の削除跡を表示し、既存返信を保持する。

## 実装方針

Laravelの標準的なMVC構成に沿う。
