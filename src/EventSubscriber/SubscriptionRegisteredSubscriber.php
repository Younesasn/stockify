<?php

namespace App\EventSubscriber;

use App\Mail\SubscriptionService;
use App\Event\SubscriptionRegisteredEvent;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Bridge\Discord\DiscordOptions;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Notifier\Bridge\Discord\Embeds\DiscordEmbed;
use Symfony\Component\Notifier\Bridge\Discord\Embeds\DiscordFieldEmbedObject;
use Symfony\Component\Notifier\Bridge\Discord\Embeds\DiscordMediaEmbedObject;
use Symfony\Component\Notifier\Bridge\Discord\Embeds\DiscordFooterEmbedObject;

class SubscriptionRegisteredSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private ChatterInterface $chatter
    ) {
    }

    public function sendConfirmationEmail(SubscriptionRegisteredEvent $event): void
    {
        $this->subscriptionService->sendConfirmation($event->getUser());
    }

    public function sendDiscordNotification(SubscriptionRegisteredEvent $event): void
    {
        $user = $event->getUser();
        $chatMessage = new ChatMessage('');

        // Create Discord Embed
        $discordOptions = (new DiscordOptions())
            ->username('Stockify')
            ->addEmbed(
                (new DiscordEmbed())
                ->color(2021216)
                ->title('Nouvel utilisateur chez Stockify !')
                ->thumbnail((new DiscordMediaEmbedObject())
                ->url('https://images.app.goo.gl/AgcHTZ8QH9NHK2ur7'))
                ->addField(
                    (new DiscordFieldEmbedObject())
                    ->name('Email')
                    ->value($user->getEmail())
                    ->inline(true)
                )
                ->addField(
                    (new DiscordFieldEmbedObject())
                    ->name('Prénom')
                    ->value($user->getFirstName())
                    ->inline(true)
                )
                ->addField(
                    (new DiscordFieldEmbedObject())
                    ->name('Nom')
                    ->value($user->getLastName())
                    ->inline(true)
                )
                ->footer(
                    (new DiscordFooterEmbedObject())
                    ->text('Stockify - ' . date('Y'))
                    ->iconUrl('https://images.app.goo.gl/AgcHTZ8QH9NHK2ur7')
                )
            )
        ;

        $chatMessage->options($discordOptions);

        $this->chatter->send($chatMessage);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SubscriptionRegisteredEvent::NAME => [
                ['sendConfirmationEmail', 10],
                ['sendDiscordNotification', 5]
            ],
        ];
    }
}
