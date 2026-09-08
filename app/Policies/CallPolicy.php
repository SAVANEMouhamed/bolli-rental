<?php

namespace App\Policies;

use App\Models\Call;
use App\Models\User;

/**
 * Le plateau partage l'historique — c'est le problème que l'outil résout. Tout
 * agent peut donc reprendre un appel : passer « en attente » à « résolu » après
 * avoir rappelé le client est le travail quotidien du service, et le réserver à
 * l'agent qui a décroché condamnerait tout appel dont le collègue est absent.
 *
 * La suppression, elle, reste à l'auteur : elle retire une ligne de l'historique
 * commun, et on n'efface que ses propres saisies. Le cahier des charges ne
 * définit aucun rôle, aucun super-agent n'est inventé ici.
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
        return true;
    }

    public function delete(User $user, Call $call): bool
    {
        return $call->user_id === $user->id;
    }
}
