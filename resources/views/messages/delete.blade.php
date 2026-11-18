@extends('layouts.app')

@section('title', 'メッセージの削除確認')

@section('content')
    <div class="channel-form-page">
        <h1 class="h-title">メッセージの削除確認</h1>
        <p class="t-caption">#{{ $messageRecord->channel->name }}</p>

        <section class="channel-danger">
            <div class="channel-danger__intro">
                <strong>このメッセージを削除します</strong>
                <p>本文は削除済み表示に変わります。投稿者・投稿時刻・返信は残ります。</p>
            </div>
            <div class="message message--preview">
                <div class="message__content">
                    <div class="message__heading">
                        <strong>{{ $messageRecord->author->name }}</strong>
                        <time datetime="{{ $messageRecord->created_at->toIso8601String() }}">{{ $messageRecord->created_at->format('Y/m/d H:i') }}</time>
                    </div>
                    <p class="message__body">{{ $messageRecord->body }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('messages.destroy', $messageRecord) }}">
                @csrf
                @method('DELETE')
                <div class="channel-form-actions">
                    <a class="btn" href="{{ $returnUrl }}">やめる</a>
                    <button class="btn btn--danger-solid" type="submit">削除する</button>
                </div>
            </form>
        </section>
    </div>
@endsection
