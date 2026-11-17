@extends('layouts.app')

@section('title', 'メンバー管理')

@section('content')
    <div class="channel-page">
        <div class="channel-page__heading">
            <div>
                <h1 class="h-title">
                    <span class="channel-symbol" aria-hidden="true">▣</span>
                    {{ $channel->name }}
                    <span class="channel-badge channel-badge--private">プライベート</span>
                </h1>
                <p class="t-caption">{{ $channel->description ?: '説明はありません' }} · 追加された人だけが読み書きできます。</p>
            </div>
            <a class="btn btn--sm" href="{{ route('channels.show', $channel) }}">チャンネルを開く</a>
        </div>

        <section class="member-card" aria-labelledby="member-search-heading">
            <h2 class="h-heading" id="member-search-heading">メンバーを追加する</h2>
            <form class="member-search" method="GET" action="{{ route('channels.members.index', $channel) }}">
                <label class="sr-only" for="member-search">表示名またはメールアドレスで探す</label>
                <input class="input" id="member-search" name="q" value="{{ old('q', $searchTerm) }}" placeholder="表示名またはメールアドレスで探す">
                <button class="btn btn--primary" type="submit">検索</button>
            </form>

            @if ($searchTerm !== '')
                <div class="member-rows" aria-label="検索結果">
                    @forelse ($candidates as $candidate)
                        <article class="member-row">
                            <div class="member-row__main">
                                <strong>{{ $candidate->name }}</strong>
                                <span>{{ $candidate->email }}</span>
                            </div>
                            <form method="POST" action="{{ route('channels.members.store', $channel) }}">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $candidate->id }}">
                                <input type="hidden" name="q" value="{{ $searchTerm }}">
                                <button class="btn btn--primary btn--sm" type="submit">追加</button>
                            </form>
                        </article>
                    @empty
                        <p class="member-empty">該当する社員はいません。</p>
                    @endforelse
                </div>
            @endif

            @error('user_id')
                <p class="err member-error"><span class="err__icon" aria-hidden="true">!</span>{{ $message }}</p>
            @enderror
        </section>

        <section class="member-section" aria-labelledby="member-list-heading">
            <h2 class="h-heading" id="member-list-heading">メンバー　{{ $members->count() }}人</h2>
            <div class="member-rows">
                @foreach ($members as $member)
                    <article class="member-row">
                        <div class="member-avatar" aria-hidden="true">{{ mb_substr($member->name, 0, 2) }}</div>
                        <div class="member-row__main">
                            <strong>
                                {{ $member->name }}
                                @if ($member->is($channel->owner))
                                    <span class="channel-badge channel-badge--private">作成者</span>
                                @endif
                            </strong>
                            <span>{{ $member->email }}</span>
                        </div>
                        @if ($member->is($channel->owner))
                            <span class="t-caption">作成者は外せません</span>
                        @else
                            <form method="POST" action="{{ route('channels.members.destroy', ['channel' => $channel, 'user' => $member, 'q' => $searchTerm]) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn--sm btn--danger-solid" type="submit">外す</button>
                            </form>
                        @endif
                    </article>
                @endforeach
            </div>
            <p class="t-caption member-note">この画面を開けるのは、プライベートチャンネルを作った人だけです。公開チャンネルにはメンバーの管理はありません。</p>
        </section>
    </div>
@endsection
