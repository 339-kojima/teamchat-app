<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $query = $request->query('q', '');
        $searchTerm = is_string($query) ? trim($query) : '';
        $hasSearched = $request->query->has('q');
        $searchError = $hasSearched && $searchTerm === ''
            ? 'キーワードを入力してください'
            : null;
        $messages = collect();

        if ($hasSearched && $searchError === null) {
            $messages = Message::query()
                ->whereNull('deleted_at')
                ->where('body', 'like', "%{$searchTerm}%")
                ->whereHas('channel', fn ($query) => $query->visibleTo($request->user()))
                ->with(['author', 'channel'])
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get();
        }

        return view('search.index', [
            'searchTerm' => $searchTerm,
            'hasSearched' => $hasSearched,
            'searchError' => $searchError,
            'messages' => $messages,
            'sidebarChannels' => Channel::query()
                ->visibleTo($request->user())
                ->orderBy('name')
                ->get(),
        ]);
    }
}
