<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=1280">
    <title>チャンネル一覧｜チームチャットアプリ</title>
    @vite('resources/css/app.css')
</head>
<body>
    <div class="channels-page">
        <header class="channels-topbar">
            <div class="channels-brand">
                <span class="brandmark" aria-hidden="true">チ</span>
                チームチャットアプリ
            </div>
            <div class="channels-account">
                <span>{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="channels-logout" type="submit">ログアウト</button>
                </form>
            </div>
        </header>

        <main class="channels-main">
            <h1 class="channels-title">チャンネル一覧</h1>
            <section class="channels-empty" aria-label="チャンネル一覧">
                チャンネルはまだありません。
            </section>
        </main>
    </div>
</body>
</html>
