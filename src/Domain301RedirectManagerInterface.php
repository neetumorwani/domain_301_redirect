<?php

namespace Drupal\domain_301_redirect;

/**
 * Interface for Domain301RedirectManager.
 */
interface Domain301RedirectManagerInterface {

  /**
   * Determine if connection should be refreshed.
   *
   * @return array
   *   Returns the list of options that domain_301_redirect provides.
   */
  public function domain_301_redirect_check_loop($domain);

}
