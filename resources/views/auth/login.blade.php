@extends('layouts.auth')

@section('title', 'ログイン')

@section('content')
    <form method="POST" action="{{ route('login.store') }}" novalidate>
        @csrf

        <div class="field @error('email') is-error @enderror">
            <label class="label" for="email">メールアドレス</label>
            <input
                class="input"
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                maxlength="254"
                autocomplete="username"
                required
                @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
            >
            @error('email')
                <span class="err" id="email-error"><span class="err__icon" aria-hidden="true">!</span>{{ $message }}</span>
            @enderror
        </div>

        <div class="field @error('password') is-error @enderror">
            <label class="label" for="password">パスワード</label>
            <input
                class="input"
                id="password"
                name="password"
                type="password"
                autocomplete="current-password"
                required
                @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
            >
            @error('password')
                <span class="err" id="password-error"><span class="err__icon" aria-hidden="true">!</span>{{ $message }}</span>
            @enderror
        </div>

        <button class="btn btn--primary btn--lg btn--block" type="submit">ログイン</button>
    </form>

    <div class="auth__foot">
        アカウントをお持ちでない方は <a href="{{ route('register') }}">新規登録</a>
    </div>
@endsection
