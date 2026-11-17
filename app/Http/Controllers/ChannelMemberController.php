<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChannelMemberController extends Controller
{
    public function index(Request $request, Channel $channel): View
    {
        $this->authorize('manageMembers', $channel);

        $searchTerm = trim((string) $request->query('q', ''));
        $members = $channel->members()->orderBy('name')->get();
        $candidates = collect();

        if ($searchTerm !== '') {
            $candidates = User::query()
                ->where(function ($query) use ($searchTerm): void {
                    $query->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('email', 'like', "%{$searchTerm}%");
                })
                ->whereDoesntHave('channels', fn ($query) => $query->whereKey($channel->getKey()))
                ->orderBy('name')
                ->get();
        }

        return view('channels.members', [
            'channel' => $channel->load('owner'),
            'members' => $members,
            'candidates' => $candidates,
            'searchTerm' => $searchTerm,
            'sidebarChannels' => Channel::query()
                ->visibleTo($request->user())
                ->with('owner')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request, Channel $channel): RedirectResponse
    {
        $this->authorize('manageMembers', $channel);

        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'q' => ['nullable', 'string'],
        ], [
            'user_id.required' => '社員を選択してください',
            'user_id.integer' => '社員を選択してください',
            'user_id.exists' => '社員を選択してください',
        ]);

        if ($channel->members()->whereKey($data['user_id'])->exists()) {
            return redirect()
                ->route('channels.members.index', ['channel' => $channel, 'q' => $data['q'] ?? null])
                ->withErrors(['user_id' => 'この社員はすでにメンバーです'])
                ->withInput();
        }

        $channel->members()->attach($data['user_id']);

        return redirect()->route('channels.members.index', [
            'channel' => $channel,
            'q' => $data['q'] ?? null,
        ]);
    }

    public function destroy(Request $request, Channel $channel, User $user): RedirectResponse
    {
        $this->authorize('removeMember', [$channel, $user]);

        $channel->members()->detach($user->getKey());

        return redirect()->route('channels.members.index', [
            'channel' => $channel,
            'q' => $request->query('q'),
        ]);
    }
}
