<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

class AddMyCorpHeaderListener
{
  public function addHeader(ResponseEvent $event)
  {
    $response = $event->getResponse();

    $response->headers->add([
      'X-DEVELOPED-BY' => 'You-Dev'
    ]);
  }
}