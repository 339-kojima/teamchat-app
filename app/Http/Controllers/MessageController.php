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

    public function thread(Message $message): View
    {
        $this->authorize('thread', $message);

        $message->load('author', 'channel');
        $replies = $message->replies()
            ->with('author')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $editingReply = null;
        if (request()->filled('edit')) {
            $editingReply = $replies->firstWhere('id', (int) request()->query('edit'));
            abort_if($editingReply === null, 404);
            $this->authorize('update', $editingReply);
        }

        return $this->page('messages.thread', [
            'parentMessage' => $message,
            'replies' => $replies,
            'editingReply' => $editingReply,
        ]);
    }

    public function storeReply(Request $request, Message $message): RedirectResponse
    {
        $this->authorize('thread', $message);

        $data = $this->validate($request, [
            'body' => ['required', 'string', 'max:1000'],
        ], $this->validationMessages());

        $message->replies()->create([
            'channel_id' => $message->channel_id,
            'user_id' => $request->user()->getKey(),
            'body' => $data['body'],
        ]);

        return redirect()->route('messages.thread', $message);
    }

    public function confirmDestroy(Message $message): View
    {
        $this->authorize('delete', $message);

        return $this->page('messages.delete', [
            'messageRecord' => $message->load(['author', 'channel']),
            'returnUrl' => $message->parent_id === null
                ? route('channels.show', $message->channel)
                : route('messages.thread', $message->parent_id),
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

        return $message->parent_id === null
            ? redirect()->route('channels.show', $message->channel)
            : redirect()->route('messages.thread', $message->parent_id);
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

        return $message->parent_id === null
            ? redirect()->route('channels.show', $message->channel)
            : redirect()->route('messages.thread', $message->parent_id);
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
