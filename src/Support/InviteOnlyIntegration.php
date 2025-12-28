<?php

declare(strict_types=1);

namespace OffloadProject\Testerra\Support;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use OffloadProject\InviteOnly\Facades\InviteOnly;
use OffloadProject\InviteOnly\Models\Invitation;
use OffloadProject\Testerra\Events\TesterInvited;

final class InviteOnlyIntegration
{
    /**
     * Invite a tester via email.
     *
     * @param  array<string, mixed>  $options
     */
    public function invite(string $email, Model $invitable, array $options = []): Invitation
    {
        $invitation = InviteOnly::invite($email, $invitable, $options);

        event(new TesterInvited($email, $options['name'] ?? $email, $options['metadata'] ?? []));

        return $invitation;
    }

    /**
     * Accept an invitation by token.
     */
    public function accept(string $token, Model $user): Invitation
    {
        return InviteOnly::accept($token, $user);
    }

    /**
     * Decline an invitation by token.
     */
    public function decline(string $token): Invitation
    {
        return InviteOnly::decline($token);
    }

    /**
     * Cancel an invitation.
     */
    public function cancel(Invitation $invitation): Invitation
    {
        return InviteOnly::cancel($invitation);
    }

    /**
     * Get pending invitations for an invitable model.
     *
     * @return Collection<int, Invitation>
     */
    public function pending(Model $invitable): Collection
    {
        return InviteOnly::pending($invitable);
    }

    /**
     * Find an invitation by token.
     */
    public function find(string $token): ?Invitation
    {
        return InviteOnly::find($token);
    }

    /**
     * Find an invitation by email for an invitable.
     */
    public function findByEmail(string $email, Model $invitable): ?Invitation
    {
        return InviteOnly::findByEmail($email, $invitable);
    }

    /**
     * Check if an email has a pending invitation for the invitable.
     */
    public function hasPendingInvitation(string $email, Model $invitable): bool
    {
        $invitation = $this->findByEmail($email, $invitable);

        return $invitation !== null && $invitation->isPending();
    }

    /**
     * Resend an invitation.
     */
    public function resend(Invitation $invitation): Invitation
    {
        return InviteOnly::resend($invitation);
    }
}
