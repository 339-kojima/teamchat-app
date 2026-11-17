<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ChannelController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Channel::class);

        $channels = $this->visibleChannels()->get();

        return view('channels.index', [
            'channels' => $channels,
            'sidebarChannels' => $channels,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Channel::class);

        return $this->page('channels.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Channel::class);

        $isPrivate = $request->boolean('is_private');
        $data = $this->validate($request, $this->channelRules($isPrivate), $this->validationMessages());

        DB::transaction(function () use ($data, $isPrivate): void {
            $channel = Channel::create([
                'owner_id' => auth()->id(),
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_private' => $isPrivate,
            ]);

            $channel->members()->attach(auth()->id());

        });

        return redirect()->route('channels.index');
    }

    public function show(Channel $channel): View
    {
        $this->authorize('view', $channel);

        return $this->page('channels.show', ['channel' => $channel->load('owner')]);
    }

    public function edit(Channel $channel): View
    {
        $this->authorize('update', $channel);

        return $this->page('channels.edit', ['channel' => $channel]);
    }

    public function update(Request $request, Channel $channel): RedirectResponse
    {
        $this->authorize('update', $channel);

        $data = $this->validate(
            $request,
            $this->channelRules($channel->is_private, $channel),
            $this->validationMessages()
        );

        $channel->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        return redirect()->route('channels.index');
    }

    public function destroy(Request $request, Channel $channel): RedirectResponse
    {
        $this->authorize('delete', $channel);

        $this->validate($request, [
            'confirmation_name' => ['required', 'string', Rule::in([$channel->name])],
        ], [
            'confirmation_name.required' => 'チャンネル名が一致しません',
            'confirmation_name.in' => 'チャンネル名が一致しません',
        ]);

        $channel->delete();

        return redirect()->route('channels.index');
    }

    private function channelRules(bool $isPrivate, ?Channel $channel = null): array
    {
        $uniqueName = Rule::unique('channels', 'name')->where('is_private', $isPrivate);

        if ($channel !== null) {
            $uniqueName->ignore($channel->getKey());
        }

        $rules = [
            'name' => ['required', 'string', 'max:50', $uniqueName],
            'description' => ['nullable', 'string', 'max:200'],
        ];

        if ($channel === null) {
            $rules['is_private'] = ['required', 'boolean'];
        }

        return $rules;
    }

    private function validationMessages(): array
    {
        return [
            'name.required' => 'チャンネル名を入力してください',
            'name.max' => 'チャンネル名は50文字以内で入力してください',
            'name.unique' => '同じ公開範囲に同じ名前のチャンネルがあります',
            'description.max' => '200文字以内で入力してください',
            'is_private.required' => '公開範囲を選択してください',
            'is_private.boolean' => '公開範囲を選択してください',
        ];
    }

    private function visibleChannels(): Builder
    {
        return Channel::query()
            ->visibleTo(auth()->user())
            ->with('owner')
            ->orderBy('name');
    }

    private function page(string $view, array $data = []): View
    {
        return view($view, array_merge($data, [
            'sidebarChannels' => $this->visibleChannels()->get(),
        ]));
    }
}
