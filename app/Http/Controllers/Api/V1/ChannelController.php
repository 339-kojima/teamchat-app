<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use Illuminate\Http\JsonResponse;

class ChannelController extends Controller
{
    public function methodNotAllowed(): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => 'method_not_allowed',
                'message' => 'このAPIは読み取り専用です。',
            ],
        ], 405);
    }

    public function index(): JsonResponse
    {
        $channels = Channel::query()
            ->where('is_private', false)
            ->orderBy('name')
            ->get(['id', 'name', 'description']);

        return response()->json(['data' => $channels]);
    }

    public function messages(string $channel): JsonResponse
    {
        $publicChannel = Channel::query()
            ->whereKey($channel)
            ->where('is_private', false)
            ->first();

        if ($publicChannel === null) {
            return response()->json([
                'error' => [
                    'code' => 'not_found',
                    'message' => 'チャンネルが見つかりません。',
                ],
            ], 404);
        }

        $messages = $publicChannel->messages()
            ->whereNull('deleted_at')
            ->with('author:id,name')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $messages->map(fn ($message) => [
                'id' => $message->id,
                'parent_id' => $message->parent_id,
                'author' => $message->author->name,
                'body' => $message->body,
                'created_at' => $message->created_at->toIso8601String(),
                'edited_at' => $message->edited_at?->toIso8601String(),
            ]),
        ]);
    }
}
