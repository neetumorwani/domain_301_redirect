<?php

namespace Drupal\domain_301_redirect;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Component\Utility\Unicode;

/**
 * Defines an Domain301RedirectManager service.
 */
class Domain301RedirectManager implements Domain301RedirectManagerInterface {

  /**
   * Constructs an Domain301RedirectManager object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The Configuration Factory.
   */
  public function __construct() {

  }

  /**
   * {@inheritdoc}
   */
  function domain_301_redirect_check_loop($domain) {
    // Get host from configured domain.
    $redirect_host =  Unicode::strtolower(parse_url($domain, PHP_URL_HOST));
    $host =   Unicode::strtolower(parse_url($GLOBALS['base_url'], PHP_URL_HOST));

    // Redirecting back to this site actually is actively ignored in hook_init, so
    // it makes no sense to allow users to set this as a value. On the other hand
    // when the admin is on the redirected domain he should still be able to alter
    // other settings without first disabling redirection. So let's just accept
    // the current host.
    if ($redirect_host == $host) {
      return FALSE;
    }

    $redirect_loop = FALSE;
    $domain_config = \Drupal::config('domain_301_redirect.settings');
    $redirects_to_check = $domain_config->get('domain_301_redirect_loop_max_redirects');
    $checked = 0;

    // Make a request to the domain that is being configured, following a
    // configured number of redirects. This has to be done individually, because
    // if checking all 3 levels at once, we might happen to get the wrong one back
    // (if the redirect loop has multiple levels).
    do {
      $client = \Drupal::httpClient();
      $response = $client->get($domain, array('max_redirects' => 0, 'method' => 'HEAD', 'headers' => array('Accept' => 'text/plain')));

      if (!empty($response->redirect_url)) {
        // Request target for the next request loop.
        $domain = $response->redirect_url;
        // Check if any host names match the redirect host name.
        $location_host = Unicode::strtolower(parse_url($response->redirect_url, PHP_URL_HOST));
        if ($redirect_host == $location_host || $host == $location_host) {
          $redirect_loop = TRUE;
        }
      }
      $checked++;
      // Don't check the redirect code, as it's possible there may be another
      // redirect service in operation that does not use 301.
    } while (!$redirect_loop && !empty($response->redirect_url) && $checked < $redirects_to_check);

    return $redirect_loop;
  }
}
