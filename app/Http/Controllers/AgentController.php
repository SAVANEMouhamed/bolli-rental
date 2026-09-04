<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAgentRequest;
use App\Models\User;
use App\Notifications\AgentInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AgentController extends Controller
{
    /**
     * Liste des agents du service client, avec le volume d'appels déjà traité.
     */
    public function index(Request $request): Response
    {
        $agents = User::query()
            ->select(['id', 'name', 'email', 'created_at'])
            ->withCount('calls')
            ->orderBy('name')
            ->get()
            ->map(fn (User $agent): array => [
                'id' => $agent->id,
                'name' => $agent->name,
                'email' => $agent->email,
                'calls_count' => $agent->calls_count,
                'is_current' => $agent->is($request->user()),
            ]);

        return Inertia::render('agents/Index', [
            'agents' => $agents,
        ]);
    }

    /**
     * Crée l'agent et lui envoie ses accès par e-mail. Le mot de passe posé ici est
     * aléatoire et jamais transmis : seul le lien de définition permet d'entrer.
     */
    public function store(StoreAgentRequest $request): RedirectResponse
    {
        $agent = User::create([
            ...$request->validated(),
            'password' => Str::password(32),
        ]);

        $this->sendInvitation($agent, $request->user()->name);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Invitation envoyée à {$agent->email}.",
        ]);

        return to_route('agents.index');
    }

    /**
     * Renvoie l'invitation, par exemple quand le lien précédent a expiré.
     */
    public function resendInvitation(Request $request, User $agent): RedirectResponse
    {
        $this->sendInvitation($agent, $request->user()->name);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Invitation renvoyée à {$agent->email}.",
        ]);

        return back();
    }

    private function sendInvitation(User $agent, string $invitedBy): void
    {
        $token = Password::broker(config('fortify.passwords'))->createToken($agent);

        $agent->notify(new AgentInvitation($token, $invitedBy));
    }
}
