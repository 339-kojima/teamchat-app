<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function store(Request $request, Channel $channel): RedirectResponse
    {
        $this->authorize('view', $channel);

        $data = $this->validate($request, [
            'body' => ['required', 'string', 'max:1000'],
        ], $this->validationMessages());

        $channel->messages()->create([
            'user_id' => $request->user()->getKey(),
            'body' => $data['body'],
        ]);

        return redirect()->route('channels.show', $channel);
    }

    public function confirmDestroy(Message $message): View
    {
        $this->authorize('delete', $message);

        return $this->page('messages.delete', [
            'messageRecord' => $message->load(['author', 'channel']),
        ]);
    }

    public function update(Request $request, Message $message): RedirectResponse
    {
        $this->authorize('update', $message);

        $data = $this->validate($request, [
            'body' => ['required', 'string', 'max:1000'],
        ], $this->validationMessages());

        $message->update([
            'body' => $data['body'],
            'edited_at' => now(),
        ]);

        return redirect()->route('channels.show', $message->channel);
    }

    public function destroy(Message $message): RedirectResponse
    {
        $this->authorize('delete', $message);

        DB::transaction(function () use ($message): void {
            $message->update([
                'body' => null,
                'deleted_at' => now(),
            ]);
        });

        return redirect()->route('channels.show', $message->channel);
    }

    private function validationMessages(): array
    {
        return [
            'body.required' => 'メッセージを入力してください',
            'body.max' => '1,000文字以内で入力してください',
        ];
    }

    private function page(string $view, array $data = []): View
    {
        return view($view, array_merge($data, [
            'sidebarChannels' => Channel::query()
                ->visibleTo(auth()->user())
                ->orderBy('name')
                ->get(),
        ]));
    }
}
