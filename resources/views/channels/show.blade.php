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
            @can('manageMembers', $channel)
                <a class="btn" href="{{ route('channels.members.index', $channel) }}">メンバー管理</a>
            @endcan
            <a class="btn" href="{{ route('channels.index') }}">チャンネル一覧へ戻る</a>
        </div>

        <section class="message-section" aria-label="メッセージ">
            <div class="message-list">
                @forelse ($messages as $channelMessage)
                    <article class="message {{ $channelMessage->deleted_at ? 'message--deleted' : '' }}" id="message-{{ $channelMessage->id }}">
                        <div class="message__avatar" aria-hidden="true">{{ mb_substr($channelMessage->author->name, 0, 1) }}</div>
                        <div class="message__content">
                            <div class="message__heading">
                                <strong>{{ $channelMessage->author->name }}</strong>
                                <time datetime="{{ $channelMessage->created_at->toIso8601String() }}">{{ $channelMessage->created_at->format('Y/m/d H:i') }}</time>
                            </div>
                            @if ($editingMessage && $editingMessage->is($channelMessage))
                                <form class="message-edit" method="POST" action="{{ route('messages.update', $channelMessage) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="field @error('body') is-error @enderror">
                                        <label class="label" for="edit-body">メッセージ<span class="req">*</span></label>
                                        <textarea class="textarea" id="edit-body" name="body" maxlength="1000" required @error('body') aria-invalid="true" aria-describedby="edit-body-error" @enderror>{{ old('body', $channelMessage->body) }}</textarea>
                                        <div class="field-foot">
                                            @error('body')
                                                <span class="err" id="edit-body-error"><span class="err__icon" aria-hidden="true">!</span>{{ $message }}</span>
                                            @else
                                                <span class="help">1,000文字以内で入力してください。</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="message-composer__actions">
                                        <a class="btn" href="{{ route('channels.show', $channel) }}">キャンセル</a>
                                        <button class="btn btn--primary" type="submit">保存する</button>
                                    </div>
                                </form>
                            @else
                                @if ($channelMessage->deleted_at)
                                    <p class="message__deleted">このメッセージは削除されました</p>
                                @else
                                    <p class="message__body">{{ $channelMessage->body }}
                                        @if ($channelMessage->edited_at)
                                            <span class="message__edited">編集済み</span>
                                        @endif
                                    </p>
                                @endif

                                @if ($channelMessage->user_id === auth()->id() && ! $channelMessage->deleted_at)
                                    <div class="message__actions">
                                        <a class="btn btn--sm" href="{{ route('channels.show', ['channel' => $channel, 'edit' => $channelMessage->id]) }}#message-{{ $channelMessage->id }}">編集</a>
                                        <a class="btn btn--sm btn--danger-solid" href="{{ route('messages.delete.confirm', $channelMessage) }}">削除</a>
                                    </div>
                                @endif
                            @endif

                            <a class="message__replies" href="{{ route('messages.thread', $channelMessage) }}">返信{{ $channelMessage->replies_count }}件</a>
                        </div>
                    </article>
                @empty
                    <p class="message-empty">メッセージはまだありません。</p>
                @endforelse
            </div>

            <form class="message-composer" method="POST" action="{{ route('messages.store', $channel) }}">
                @csrf
                <div class="field @error('body') is-error @enderror">
                    <label class="label" for="body">メッセージ</label>
                    <textarea class="textarea" id="body" name="body" maxlength="1000" required placeholder="#{{ $channel->name }} にメッセージを送る" @error('body') aria-invalid="true" aria-describedby="body-error" @enderror>{{ old('body') }}</textarea>
                    <div class="field-foot">
                        @error('body')
                            <span class="err" id="body-error"><span class="err__icon" aria-hidden="true">!</span>{{ $message }}</span>
                        @else
                            <span class="help">1,000文字以内で入力してください。</span>
                        @enderror
                    </div>
                </div>
                <div class="message-composer__actions">
                    <button class="btn btn--primary" type="submit">送信</button>
                </div>
            </form>
        </section>
    </div>
@endsection
