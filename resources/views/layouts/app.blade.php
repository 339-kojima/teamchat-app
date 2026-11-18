<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=1280">
    <title>@yield('title')｜チームチャットアプリ</title>
    @vite('resources/css/app.css')
</head>
<body class="app-body">
    <div class="app-shell">
        <header class="app-topbar">
            <a class="app-brand" href="{{ route('channels.index') }}">
                <span class="brandmark" aria-hidden="true">チ</span>
                チームチャットアプリ
            </a>
            <a class="app-search-link" href="{{ route('search.index') }}">
                <span aria-hidden="true">⌕</span>
                メッセージを検索
            </a>
            <div class="app-account">
                <span>{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="app-logout" type="submit">ログアウト</button>
                </form>
            </div>
        </header>

        <div class="app-frame">
            <nav class="app-sidebar" aria-label="チャンネル">
                <div class="sidebar-heading">チャンネル</div>
                @forelse ($sidebarChannels as $sidebarChannel)
                    <a class="sidebar-channel" href="{{ route('channels.show', $sidebarChannel) }}">
                        <span aria-hidden="true">{{ $sidebarChannel->is_private ? '▣' : '#' }}</span>
                        <span>{{ $sidebarChannel->name }}</span>
                    </a>
                @empty
                    <p class="sidebar-empty">チャンネルはまだありません。</p>
                @endforelse
                <a class="sidebar-index" href="{{ route('channels.index') }}">チャンネル一覧を見る</a>
                <a class="sidebar-create" href="{{ route('channels.create') }}">＋ チャンネルを作る</a>
            </nav>

            <main class="app-main">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
