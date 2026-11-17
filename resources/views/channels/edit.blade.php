@extends('layouts.app')

@section('title', 'チャンネルを編集する')

@section('content')
    <div class="channel-form-page">
        <h1 class="h-title">チャンネルを編集する</h1>

        <form class="channel-card" method="POST" action="{{ route('channels.update', $channel) }}">
            @csrf
            @method('PUT')

            <div class="field @error('name') is-error @enderror">
                <label class="label" for="name">チャンネル名<span class="req">*</span></label>
                <input class="input" id="name" name="name" value="{{ old('name', $channel->name) }}" maxlength="50" required @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                <div class="field-foot">
                    @error('name')
                        <span class="err" id="name-error"><span class="err__icon" aria-hidden="true">!</span>{{ $message }}</span>
                    @else
                        <span class="help">{{ $channel->is_private ? 'プライベート' : '公開' }}チャンネルです。公開範囲はあとから変更できません。</span>
                    @enderror
                </div>
            </div>

            <div class="field @error('description') is-error @enderror">
                <label class="label" for="description">説明</label>
                <textarea class="textarea" id="description" name="description" maxlength="200" @error('description') aria-invalid="true" aria-describedby="description-error" @enderror>{{ old('description', $channel->description) }}</textarea>
                <div class="field-foot">
                    @error('description')
                        <span class="err" id="description-error"><span class="err__icon" aria-hidden="true">!</span>{{ $message }}</span>
                    @else
                        <span class="help">200文字以内で入力してください。</span>
                    @enderror
                    <span class="count">200文字以内</span>
                </div>
            </div>

            <div class="channel-form-actions">
                <a class="btn" href="{{ route('channels.show', $channel) }}">キャンセル</a>
                <button class="btn btn--primary" type="submit">保存する</button>
            </div>
        </form>

        <section class="channel-danger">
            <h2 class="h-heading">チャンネルを削除する</h2>
            <div class="channel-danger__intro">
                <strong>このチャンネルを削除します</strong>
                <p>メッセージと返信もすべて削除され、元に戻せません。</p>
            </div>
            <form method="POST" action="{{ route('channels.destroy', $channel) }}">
                @csrf
                @method('DELETE')
                <div class="field @error('confirmation_name') is-error @enderror">
                    <label class="label" for="confirmation_name">「{{ $channel->name }}」を削除します。確認のためチャンネル名を入力してください。</label>
                    <input
                        class="input"
                        id="confirmation_name"
                        name="confirmation_name"
                        value="{{ old('confirmation_name') }}"
                        placeholder="{{ $channel->name }}"
                        maxlength="50"
                        required
                        @error('confirmation_name') aria-invalid="true" aria-describedby="confirmation-name-error" @enderror
                    >
                    @error('confirmation_name')
                        <span class="err" id="confirmation-name-error"><span class="err__icon" aria-hidden="true">!</span>{{ $message }}</span>
                    @enderror
                </div>
                <div class="channel-form-actions">
                    <a class="btn" href="{{ route('channels.show', $channel) }}">やめる</a>
                    <button class="btn btn--danger-solid" type="submit">削除する</button>
                </div>
            </form>
        </section>
    </div>
@endsection
