@extends('layouts.app')

@section('title', 'チャンネル一覧')

@section('content')
    <div class="channel-page">
        <div class="channel-page__heading">
            <div>
                <h1 class="h-title">チャンネル一覧</h1>
                <p class="t-caption">公開チャンネルは、ログインしている社員なら誰でも読み書きできます。</p>
            </div>
            <a class="btn btn--primary" href="{{ route('channels.create') }}">＋ チャンネルを作る</a>
        </div>

        @if ($channels->isEmpty())
            <section class="channel-empty" aria-label="チャンネル一覧">
                <h2 class="h-heading">チャンネルはまだありません。</h2>
                <p class="t-caption">チャンネルを作ると、ここに表示されます。</p>
            </section>
        @else
            <div class="channel-rows">
                @foreach ($channels as $channel)
                    <article class="channel-row">
                        <div class="channel-row__main">
                            <h2 class="channel-row__title">
                                <span class="channel-symbol" aria-hidden="true">{{ $channel->is_private ? '▣' : '#' }}</span>
                                {{ $channel->name }}
                                <span class="channel-badge {{ $channel->is_private ? 'channel-badge--private' : 'channel-badge--public' }}">
                                    {{ $channel->is_private ? 'プライベート' : '公開' }}
                                </span>
                            </h2>
                            <p class="channel-row__description">
                                {{ $channel->description ?: '説明はありません' }}
                                <span>· 作成者：{{ $channel->owner->name }}</span>
                            </p>
                        </div>
                        <div class="channel-row__actions">
                            @can('update', $channel)
                                <a class="btn btn--sm" href="{{ route('channels.edit', $channel) }}">編集</a>
                            @endcan
                            <a class="btn btn--sm" href="{{ route('channels.show', $channel) }}">開く</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection
