<?php 

namespace App\Mail;
use App\Entity\User;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

class SubscriptionService {
    public function __construct(
        private MailerInterface $mailer,
        private string $adminEmail
    ) {
    }

    /**
     * Fonction qui permet l'envoie d'un mail d'inscription à l'utilisateur courant 
     *
     * @param User $user
     * @return void
     */
    public function sendConfirmation(User $user): void
    {
        $email = (new Email())
            ->from($this->adminEmail)
            ->to($user->getEmail())
            ->subject('Stockify - Inscription')
            ->html('Votre inscription a bien été enregistrée. Merci pour votre confiance !');

        $this->mailer->send($email);
    }
}