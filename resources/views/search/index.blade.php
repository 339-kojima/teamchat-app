@extends('layouts.app')

@section('title', 'メッセージを検索')

@section('content')
    <div class="search-page">
        <h1 class="h-title">メッセージを検索</h1>

        <form class="search-form" method="GET" action="{{ route('search.index') }}">
            <label class="sr-only" for="search-query">キーワード</label>
            <input
                class="input"
                id="search-query"
                name="q"
                value="{{ $searchTerm }}"
                required
                @if ($searchError) aria-invalid="true" aria-describedby="search-query-error" @endif
            >
            <button class="btn btn--primary" type="submit">検索</button>
        </form>
        @if ($searchError)
            <p class="err search-error" id="search-query-error"><span class="err__icon" aria-hidden="true">!</span>{{ $searchError }}</p>
        @endif

        @if ($hasSearched && !$searchError)
            <p class="t-caption search-count">「{{ $searchTerm }}」の検索結果　{{ $messages->count() }}件</p>

            @if ($messages->isEmpty())
                <section class="search-empty" aria-label="検索結果">
                    <h2 class="h-heading">「{{ $searchTerm }}」に一致するメッセージはありません</h2>
                    <p class="t-caption">別のキーワードで試すか、キーワードを短くしてください。</p>
                </section>
            @else
                <div class="search-results">
                    @foreach ($messages as $message)
                        @php
                            $resultUrl = $message->parent_id === null
                                ? route('channels.show', $message->channel).'#message-'.$message->id
                                : route('messages.thread', $message->parent_id).'#message-'.$message->id;
                        @endphp
                        <article class="search-result">
                            <a class="search-result__link" href="{{ $resultUrl }}">
                                <span class="search-result__meta">
                                    <span class="search-result__channel">
                                        <span aria-hidden="true">{{ $message->channel->is_private ? '▣' : '#' }}</span>
                                        {{ $message->channel->name }}
                                    </span>
                                    <span aria-hidden="true">·</span>
                                    <span>{{ $message->author->name }}</span>
                                    <span aria-hidden="true">·</span>
                                    <time datetime="{{ $message->created_at->toIso8601String() }}">{{ $message->created_at->format('Y/m/d H:i') }}</time>
                                </span>
                                <span class="search-result__body">{{ $message->body }}</span>
                            </a>
                        </article>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
@endsection
