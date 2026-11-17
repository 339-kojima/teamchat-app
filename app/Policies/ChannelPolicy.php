<?php

namespace App\Policies;

use App\Models\Channel;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ChannelPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Channel $channel): Response
    {
        if ($channel->newQuery()->visibleTo($user)->whereKey($channel->getKey())->exists()) {
            return Response::allow();
        }

        return Response::denyAsNotFound();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Channel $channel): Response
    {
        return $channel->owner_id === $user->id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function delete(User $user, Channel $channel): Response
    {
        return $this->update($user, $channel);
    }

    public function manageMembers(User $user, Channel $channel): Response
    {
        if (! $channel->is_private || $channel->owner_id !== $user->id) {
            return Response::denyAsNotFound();
        }

        return Response::allow();
    }

    public function removeMember(User $user, Channel $channel, User $member): Response
    {
        if (! $this->manageMembers($user, $channel)->allowed()) {
            return Response::denyAsNotFound();
        }

        if ($member->is($channel->owner) || ! $channel->members()->whereKey($member->getKey())->exists()) {
            return Response::denyAsNotFound();
        }

        return Response::allow();
    }
}
