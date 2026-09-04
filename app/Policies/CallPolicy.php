<?php

namespace App\Policies;

use App\Models\Call;
use App\Models\User;

/**
 * Le plateau partage la visibilité — c'est le problème que l'outil résout — mais
 * chaque agent ne corrige que les appels qu'il a lui-même enregistrés.
 * Le cahier des charges ne définit aucun rôle : pas de super-agent inventé ici.
 */
class CallPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Call $call): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Call $call): bool
    {
        return $call->user_id === $user->id;
    }

    public function delete(User $user, Call $call): bool
    {
        return $call->user_id === $user->id;
    }
}
