@extends('layouts.app')

@section('title', $channel->name)

@section('content')
    <div class="channel-page">
        <div class="channel-detail">
            <div>
                <h1 class="h-title">
                    <span class="channel-symbol" aria-hidden="true">{{ $channel->is_private ? '▣' : '#' }}</span>
                    {{ $channel->name }}
                </h1>
                <p class="t-caption">{{ $channel->description ?: '説明はありません' }}</p>
            </div>
            <span class="channel-badge {{ $channel->is_private ? 'channel-badge--private' : 'channel-badge--public' }}">
                {{ $channel->is_private ? 'プライベート' : '公開' }}
            </span>
        </div>

        <dl class="channel-metadata">
            <div>
                <dt>作成者</dt>
                <dd>{{ $channel->owner->name }}</dd>
            </div>
            <div>
                <dt>公開範囲</dt>
                <dd>{{ $channel->is_private ? 'プライベート' : '公開' }}</dd>
            </div>
        </dl>

        <div class="channel-detail__actions">
            @can('update', $channel)
                <a class="btn" href="{{ route('channels.edit', $channel) }}">編集</a>
            @endcan
            <a class="btn" href="{{ route('channels.index') }}">チャンネル一覧へ戻る</a>
        </div>
    </div>
@endsection
