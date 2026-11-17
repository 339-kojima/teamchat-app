<?php

namespace App\Policies;

use App\Models\Channel;
use App\Models\Message;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MessagePolicy
{
    public function update(User $user, Message $message): Response
    {
        return $this->canManage($user, $message)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function delete(User $user, Message $message): Response
    {
        return $this->canManage($user, $message)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    private function canManage(User $user, Message $message): bool
    {
        if ($message->user_id !== $user->getKey() || $message->deleted_at !== null) {
            return false;
        }

        return Channel::query()
            ->visibleTo($user)
            ->whereKey($message->channel_id)
            ->exists();
    }
}
