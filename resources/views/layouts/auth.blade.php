<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=1280">
    <title>@yield('title')｜チームチャットアプリ</title>
    @vite('resources/css/app.css')
</head>
<body>
    <main class="auth">
        <section class="auth__card" aria-labelledby="page-title">
            <div class="auth__brand">
                <span class="brandmark" aria-hidden="true">チ</span>
                チームチャットアプリ
            </div>
            <h1 id="page-title" class="h-display auth__title">@yield('title')</h1>
            @yield('content')
        </section>
    </main>
</body>
</html>
