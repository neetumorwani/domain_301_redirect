<?php

namespace Drupal\domain_301_redirect\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class DomainRedirectEventSubscriber.
 *
 * @package Drupal\domain_301_redirect
 */
class DomainRedirectEventSubscriber implements EventSubscriberInterface {

  /**
   * The config factory object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * DomainRedirectEventSubscriber constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory object.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(ConfigFactoryInterface $config_factory, RequestStack $request_stack) {
    $this->configFactory = $config_factory;
    $this->request = $request_stack->getCurrentRequest();
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
    $domain_config = $this->configFactory->get('domain_301_redirect.settings')->getRawData();
    //if ($domain_config['domain_301_redirect_enabled'] && $domain_config['domain_301_redirect_domain']) {
      if (!preg_match('|^https?://|', $domain_config['domain_301_redirect_domain'])) {
        $domain_config['domain_301_redirect_domain'] = 'http://' . $domain_config['domain_301_redirect_domain'];
      }
      $port = $this->request->getPort();print_r($port);die("Fghfhg");
      //$response = new RedirectResponse("http://www.google.com",301);
      //$response->send();
    //}
  }

}
