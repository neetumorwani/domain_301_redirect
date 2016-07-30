<?php

namespace Drupal\domain_301_redirect\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * Class DomainRedirectEventSubscriber.
 *
 * @package Drupal\domain_301_redirect
 */
class DomainRedirectEventSubscriber implements EventSubscriberInterface {


  /**
   * Constructor.
   */
  public function __construct() {

  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events['kernel.request'] = ['requestHandler'];

    return $events;
  }

  /**
   * This method is called whenever the kernel.request event is
   * dispatched.
   * @todo remove hardcoded URL and create a configuration form to manage redirection
   * @param GetResponseEvent $event
   */
  public function requestHandler(GetResponseEvent $event) {
     $response = new RedirectResponse("http://www.google.com",301);
     $response->send();
  }

}
