@extends('layouts.app')

@section('title', 'チャンネルを作る')

@section('content')
    <div class="channel-form-page">
        <h1 class="h-title">チャンネルを作る</h1>

        <form class="channel-card" method="POST" action="{{ route('channels.store') }}">
            @csrf

            <div class="field @error('name') is-error @enderror">
                <label class="label" for="name">チャンネル名<span class="req">*</span></label>
                <input class="input" id="name" name="name" value="{{ old('name') }}" maxlength="50" required @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                <div class="field-foot">
                    @error('name')
                        <span class="err" id="name-error"><span class="err__icon" aria-hidden="true">!</span>{{ $message }}</span>
                    @else
                        <span class="help">50文字以内で入力してください。</span>
                    @enderror
                </div>
            </div>

            <div class="field @error('description') is-error @enderror">
                <label class="label" for="description">説明</label>
                <textarea class="textarea" id="description" name="description" maxlength="200" @error('description') aria-invalid="true" aria-describedby="description-error" @enderror>{{ old('description') }}</textarea>
                <div class="field-foot">
                    @error('description')
                        <span class="err" id="description-error"><span class="err__icon" aria-hidden="true">!</span>{{ $message }}</span>
                    @else
                        <span class="help">何について話す場所かを短く書きます。</span>
                    @enderror
                    <span class="count">200文字以内</span>
                </div>
            </div>

            <fieldset class="field channel-visibility @error('is_private') is-error @enderror">
                <legend class="label">公開範囲<span class="req">*</span></legend>
                <div class="radio-set" @error('is_private') aria-describedby="is-private-error" @enderror>
                    <label class="radio">
                        <input type="radio" name="is_private" value="0" @checked(old('is_private', '0') === '0') required>
                        <span>
                            <span class="radio__title">公開</span>
                            <span class="radio__description">ログインしている社員なら誰でも読み書きできます。</span>
                        </span>
                    </label>
                    <label class="radio">
                        <input type="radio" name="is_private" value="1" @checked(old('is_private') === '1')>
                        <span>
                            <span class="radio__title">プライベート</span>
                            <span class="radio__description">追加された人だけが読み書きできます。作成者がメンバーを管理します。</span>
                        </span>
                    </label>
                </div>
                @error('is_private')
                    <span class="err" id="is-private-error"><span class="err__icon" aria-hidden="true">!</span>{{ $message }}</span>
                @enderror
            </fieldset>

            <div class="channel-form-actions">
                <a class="btn" href="{{ route('channels.index') }}">キャンセル</a>
                <button class="btn btn--primary" type="submit">作成する</button>
            </div>
        </form>
    </div>
@endsection
