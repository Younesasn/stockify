<?php

namespace App\EventSubscriber;

use App\Entity\Upload;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Core\Security;

class DeleteUploadSubscriber implements EventSubscriberInterface
{
  public function __construct(private Security $security, private Filesystem $filesystem)
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

    if (!$entity instanceof Upload) {
      return;
    }

    $this->filesystem->remove('uploads/' . $this->security->getUser()->getDirectoryName() . '/' . $entity->getFilename());
  }
}