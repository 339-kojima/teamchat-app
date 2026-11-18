@extends('layouts.app')

@section('title', 'スレッド')

@section('content')
    <section class="thread-page" aria-label="スレッド">
        <header class="thread-page__header">
            <div>
                <h1 class="h-title">スレッド</h1>
                <p class="t-caption">#{{ $parentMessage->channel->name }}</p>
            </div>
            <a class="btn" href="{{ route('channels.show', $parentMessage->channel) }}">閉じる</a>
        </header>

        <div class="thread-page__origin">
            <article class="message {{ $parentMessage->deleted_at ? 'message--deleted' : '' }}">
                <div class="message__avatar" aria-hidden="true">{{ mb_substr($parentMessage->author->name, 0, 1) }}</div>
                <div class="message__content">
                    <div class="message__heading">
                        <strong>{{ $parentMessage->author->name }}</strong>
                        <time datetime="{{ $parentMessage->created_at->toIso8601String() }}">{{ $parentMessage->created_at->format('Y/m/d H:i') }}</time>
                    </div>
                    @if ($parentMessage->deleted_at)
                        <p class="message__deleted">このメッセージは削除されました</p>
                    @else
                        <p class="message__body">{{ $parentMessage->body }}
                            @if ($parentMessage->edited_at)
                                <span class="message__edited">編集済み</span>
                            @endif
                        </p>
                    @endif
                </div>
            </article>
        </div>

        <p class="thread-page__count">返信{{ $replies->count() }}件</p>
        <div class="message-list thread-page__replies">
            @forelse ($replies as $reply)
                <article class="message {{ $reply->deleted_at ? 'message--deleted' : '' }}" id="message-{{ $reply->id }}">
                    <div class="message__avatar" aria-hidden="true">{{ mb_substr($reply->author->name, 0, 1) }}</div>
                    <div class="message__content">
                        <div class="message__heading">
                            <strong>{{ $reply->author->name }}</strong>
                            <time datetime="{{ $reply->created_at->toIso8601String() }}">{{ $reply->created_at->format('Y/m/d H:i') }}</time>
                        </div>
                        @if ($editingReply && $editingReply->is($reply))
                            <form class="message-edit" method="POST" action="{{ route('messages.update', $reply) }}">
                                @csrf
                                @method('PUT')
                                <div class="field @error('body') is-error @enderror">
                                    <label class="label" for="edit-body">メッセージ<span class="req">*</span></label>
                                    <textarea class="textarea" id="edit-body" name="body" maxlength="1000" required>{{ old('body', $reply->body) }}</textarea>
                                    <div class="field-foot">
                                        @error('body')
                                            <span class="err"><span class="err__icon" aria-hidden="true">!</span>{{ $message }}</span>
                                        @else
                                            <span class="help">1,000文字以内で入力してください。</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="message-composer__actions">
                                    <a class="btn" href="{{ route('messages.thread', $parentMessage) }}">キャンセル</a>
                                    <button class="btn btn--primary" type="submit">保存する</button>
                                </div>
                            </form>
                        @elseif ($reply->deleted_at)
                            <p class="message__deleted">このメッセージは削除されました</p>
                        @else
                            <p class="message__body">{{ $reply->body }}
                                @if ($reply->edited_at)
                                    <span class="message__edited">編集済み</span>
                                @endif
                            </p>
                            @if ($reply->user_id === auth()->id())
                                <div class="message__actions">
                                    <a class="btn btn--sm" href="{{ route('messages.thread', ['message' => $parentMessage, 'edit' => $reply->id]) }}#message-{{ $reply->id }}">編集</a>
                                    <a class="btn btn--sm btn--danger-solid" href="{{ route('messages.delete.confirm', $reply) }}">削除</a>
                                </div>
                            @endif
                        @endif
                    </div>
                </article>
            @empty
                <p class="message-empty">返信はまだありません。</p>
            @endforelse
        </div>

        <form class="message-composer" method="POST" action="{{ route('messages.replies.store', $parentMessage) }}">
            @csrf
            <div class="field @error('body') is-error @enderror">
                <label class="label" for="reply-body">返信</label>
                <textarea class="textarea" id="reply-body" name="body" maxlength="1000" required placeholder="返信を送る" @error('body') aria-invalid="true" aria-describedby="reply-body-error" @enderror>{{ old('body') }}</textarea>
                <div class="field-foot">
                    @error('body')
                        <span class="err" id="reply-body-error"><span class="err__icon" aria-hidden="true">!</span>{{ $message }}</span>
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
@endsection
