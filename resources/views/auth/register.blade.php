@extends('layouts.auth')

@section('title', '新規登録')

@section('content')
    <form method="POST" action="{{ route('register.store') }}" novalidate>
        @csrf

        <div class="field @error('name') is-error @enderror">
            <label class="label" for="name">表示名<span class="req">*</span></label>
            <input
                class="input"
                id="name"
                name="name"
                type="text"
                value="{{ old('name') }}"
                maxlength="30"
                autocomplete="name"
                required
                @error('name') aria-invalid="true" aria-describedby="name-error" @enderror
            >
            <div class="field-foot">
                <span class="help">メッセージに表示される名前です。</span>
                <span class="count">{{ mb_strlen(old('name', '')) }} / 30</span>
            </div>
            @error('name')
                <span class="err" id="name-error"><span class="err__icon" aria-hidden="true">!</span>{{ $message }}</span>
            @enderror
        </div>

        <div class="field @error('email') is-error @enderror">
            <label class="label" for="email">メールアドレス<span class="req">*</span></label>
            <input
                class="input"
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                maxlength="254"
                autocomplete="email"
                required
                @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
            >
            @error('email')
                <span class="err" id="email-error"><span class="err__icon" aria-hidden="true">!</span>{{ $message }}</span>
            @enderror
        </div>

        <div class="field @error('password') is-error @enderror">
            <label class="label" for="password">パスワード<span class="req">*</span></label>
            <input
                class="input"
                id="password"
                name="password"
                type="password"
                autocomplete="new-password"
                required
                @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
            >
            <span class="help">8文字以上で入力してください。</span>
            @error('password')
                <span class="err" id="password-error"><span class="err__icon" aria-hidden="true">!</span>{{ $message }}</span>
            @enderror
        </div>

        <div class="field @error('password_confirmation') is-error @enderror">
            <label class="label" for="password_confirmation">パスワード（確認用）<span class="req">*</span></label>
            <input
                class="input"
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                autocomplete="new-password"
                required
                @error('password_confirmation') aria-invalid="true" aria-describedby="password-confirmation-error" @enderror
            >
            @error('password_confirmation')
                <span class="err" id="password-confirmation-error"><span class="err__icon" aria-hidden="true">!</span>{{ $message }}</span>
            @enderror
        </div>

        <button class="btn btn--primary btn--lg btn--block" type="submit">登録する</button>
    </form>

    <div class="auth__foot">
        すでにアカウントをお持ちの方は <a href="{{ route('login') }}">ログイン</a>
    </div>
@endsection
