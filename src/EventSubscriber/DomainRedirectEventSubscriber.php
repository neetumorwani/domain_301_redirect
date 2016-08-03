<?php

namespace Drupal\domain_301_redirect\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use \Drupal\Core\Session\AccountProxyInterface;
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
   * Current user acocunt.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $userAccount;
  /**
   * DomainRedirectEventSubscriber constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory object.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Session\AccountProxyInterface
   *   The current user object.
   */
  public function __construct(ConfigFactoryInterface $config_factory, RequestStack $request_stack, AccountProxyInterface $user_account) {
    $this->configFactory = $config_factory;
    $this->request = $request_stack->getCurrentRequest();
    $this->userAccount = $user_account;
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
   *
   * @todo Needs a service which will handle the exclusion/inclusion of
   * the mentioned path/page.
   *
   * @param GetResponseEvent $event
   */
  public function requestHandler(GetResponseEvent $event) {
    // If user has 'bypass' permission, then no need to process further.
    if ($this->userAccount->hasPermission('bypass domain 301 redirect')) {
      return;
    }

    $domain_config = $this->configFactory->get('domain_301_redirect.settings')->getRawData();
    // If domain redirection is not enabled, then no need to process further.
    if (!$domain_config['domain_301_redirect_enabled']) {
      return;
    }

    if (!preg_match('|^https?://|', $domain_config['domain_301_redirect_domain'])) {
      $domain_config['domain_301_redirect_domain'] = 'http://' . $domain_config['domain_301_redirect_domain'];
    }

    $domain_parts = parse_url($domain_config['domain_301_redirect_domain']);
    $parsed_domain = $domain_parts['host'];
    $parsed_domain .= !empty($domain_parts['port']) ? ':' . $domain_parts['port'] : '';

    // If we're not on the same host, the user has access and this page isn't
    // an exception, redirect.
    if (($parsed_domain != $this->request->server->get('HTTP_HOST'))) {
      $response = new RedirectResponse($domain_config['domain_301_redirect_domain'], 301);
      $response->send();
    }
  }

}
