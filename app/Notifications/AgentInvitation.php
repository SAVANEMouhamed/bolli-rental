<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Invitation d'un agent du service client : le compte existe déjà avec un mot de
 * passe aléatoire que personne ne connaît, l'agent choisit le sien via le lien.
 */
class AgentInvitation extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $token,
        private readonly string $invitedBy,
    ) {}

    /**
     * @return list<string>
     */
    public function via(User $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $minutes = config('auth.passwords.'.config('fortify.passwords').'.expire');

        return (new MailMessage)
            ->subject('Votre accès à '.config('app.name'))
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line($this->invitedBy.' vous a ouvert un accès à '.config('app.name').', l\'outil de suivi des appels du service client.')
            ->line('Votre identifiant de connexion est votre adresse e-mail : **'.$notifiable->email.'**')
            ->action('Définir mon mot de passe', $this->resetUrl($notifiable))
            ->line('Ce lien est valable '.$minutes.' minutes. Passé ce délai, utilisez « Mot de passe oublié ? » sur la page de connexion pour en recevoir un nouveau.')
            ->line("Si vous n'attendiez pas cet e-mail, vous pouvez l'ignorer : aucun accès n'est actif tant que le mot de passe n'est pas défini.");
    }

    /**
     * Le lien réutilise le flux de réinitialisation de Fortify : jeton haché en base,
     * à usage unique et expirant, plutôt qu'un mécanisme d'invitation parallèle.
     */
    private function resetUrl(User $notifiable): string
    {
        return route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
    }
}
