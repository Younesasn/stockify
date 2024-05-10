<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Core\Security;

class DeleteUserSubscriber implements EventSubscriberInterface
{
    public function __construct(private Security $security, private Filesystem $filesystem, private EntityManagerInterface $em)
    {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::preRemove,
        ];
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof User) {
            return;
        }

        foreach ($entity->getUploads()->toArray() as $upload) {
            $this->em->remove($upload);
        }

        $this->filesystem->remove('uploads/' . $entity->getDirectoryName());
    }
}