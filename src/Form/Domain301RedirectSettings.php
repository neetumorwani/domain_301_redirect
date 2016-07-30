<?php

/**
 * @file
 * Contains \Drupal\domain_301_redirect\Form\Domain301RedirectSettings.
 */

namespace Drupal\domain_301_redirect\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;

class Domain301RedirectSettings extends ConfigFormBase {
  public function getFormId() {
    return 'domain_301_redirect_admin_form';
  }
  public function getEditableConfigNames() {
    return [
      'domain_301_redirect.settings',
    ];

  }
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
    $config = \Drupal::config('domain_301_redirect.settings');

    $disabled_by_check = $config->get('domain_301_redirect_disabled_by_check');
		$enabled = $config->get('domain_301_redirect_enabled');

	  // Warn the user if the redirect was disabled by cron.
	  if (!$enabled && $disabled_by_check) {
	    $domain = $config->get('domain_301_redirect_domain');
	    $last_checked = $config->get('domain_301_redirect_last_checked');
	    drupal_set_message(t('Redirects have been disabled by cron because the domain was not available at: %date.', array('%date' => format_date($last_checked))), 'warning');
	  }

    $form['domain_301_redirect_enabled'] = array(
	    '#type' => 'radios',
	    '#title' => t('Status'),
	    '#description' => t('Enable this setting to start 301 redirects to the domain below for all other domains.'),
	    '#options' => array(
	      1 => t('Enabled'),
	      0 => t('Disabled'),
	    ),
	    '#default_value' => $config->get('domain_301_redirect_enabled'),
	  );

	  $form['domain_301_redirect_domain'] = array(
	    '#type' => 'textfield',
	    '#title' => t('Domain'),
	    '#description' => t('This is the domain that all other domains that point to this site will be 301 redirected to. This value should also include the scheme (http or https). E.g. http://www.testsite.com'),
	    '#default_value' => $config->get('domain_301_redirect_domain'),
	  );

	  $form['domain_301_redirect_check_period'] = array(
	    '#type' => 'select',
	    '#title' => t('Domain Check'),
	    '#description' => t('This option selects the period between domain validation checks. If the Domain no longer points to this site, Domain 301 Redirection will be disabled.'),
	    '#options' => array(
	      0 => t('Disabled'),
	      3600 => t('1 hour'),
	      7200 => t('2 hours'),
	      10800 => t('3 hours'),
	      21600 => t('6 hours'),
	      43200 => t('12 hours'),
	      86400 => t('1 day'),
	    ),
	    '#default_value' => $config->get('domain_301_redirect_check_period'),
  	);
  	$form['domain_301_redirect_domain_check_retries'] = array(
      '#type' => 'select',
      '#title' => t('Domain retries'),
      '#description' => t('Number of times to check domain availability before disabling redirects.'),
      '#options' => array(1 => 1, 2 => 2, 3 => 3),
      '#default_value' => $config->get('domain_301_redirect_domain_check_retries'),
  	);
  	$form['domain_301_redirect_domain_check_reenable'] = array(
      '#type' => 'checkbox',
      '#title' => t('Re-enable domain redirection'),
      '#description' => t('Turn domain redirection on when the domain becomes available.'),
      '#default_value' => $config->get('domain_301_redirect_domain_check_reenable'),
  	);

	  // Per-path configuration settings to apply the redirect to specific paths.
	  $form['applicability']['path'] = array(
	    '#type' => 'fieldset',
	    '#title' => t('Pages'),
	    '#collapsible' => TRUE,
	    '#collapsed' => FALSE,
	    '#weight' => 0,
	  );

	  $options = array(
	    BLOCK_VISIBILITY_NOTLISTED => t('All pages except those listed'),
	    BLOCK_VISIBILITY_LISTED => t('Only the listed pages'),
	  );
	  $description = t("Specify pages by using their paths. Enter one path per line. The '*' character is a wildcard. Example paths are %blog for the blog page and %blog-wildcard for every personal blog. %front is the front page.", array('%blog' => 'blog', '%blog-wildcard' => 'blog/*', '%front' => '<front>'));

	  $title = t('Pages');

	  $form['applicability']['path']['domain_301_redirect_applicability'] = array(
      '#type' => 'radios',
      '#title' => t('Redirect on specific pages'),
      '#options' => $options,
      '#default_value' => $config->get('domain_301_redirect_applicability'),
	  );
	  $form['applicability']['path']['domain_301_redirect_pages'] = array(
	      '#type' => 'textarea',
	      '#title' => '<span class="element-invisible">' . $title . '</span>',
	      '#default_value' => $config->get('domain_301_redirect_pages'),
	      '#description' => $description,
	  );

    return parent::buildForm($form, $form_state);
  }
  /**
   * Todo
  */
  // public function validateForm(array &$form, FormStateInterface $form_state) {
  // 	$userInputValues = $form_state->getUserInput();
  // 	dsm($userInputValues);
  // 	if (!empty($userInputValues['domain_301_redirect_enabled'])) {
	 //    $domain = trim($userInputValues['domain_301_redirect_domain']);
	 //    if (!preg_match('|^https?://|', $domain)) {
	 //      $domain = 'http://' . $domain;
	 //    }
	 //    if (!valid_url($domain, TRUE)) {
	 //      form_set_error('domain_301_redirect_enabled', t('Domain 301 redirection can not be enabled if no valid domain is set.'));
	 //    }
	 //    else {
	 //      $domain_parts = parse_url($domain);
	 //      $domain = $domain_parts['scheme'] . '://' . $domain_parts['host'] . (!empty($domain_parts['port']) ? ':' . $domain_parts['port'] : '');
	 //      form_set_value($form['domain_301_redirect_domain'], $domain, $form_state);

	 //      if (!domain_301_redirect_check_domain($domain)) {
	 //        form_set_error('domain_301_redirect_enabled', t('Domain 301 redirection can not be enabled as the domain you set does not currently point to this site.'));
	 //      }
	 //      else {
	 //        // Clean up if someone is manually disabling. We don't want the system to
	 //        // re-enable if the disabling was via the admin form.
	 //        variable_set('domain_301_redirect_disabled_by_check', false);
	 //      }

	 //      if (domain_301_redirect_check_loop($domain)) {
	 //        form_set_error('domain_301_redirect_domain', t('The domain cannot be set, as it causes a redirect loop (within @num redirects).', array('@num' => variable_get('domain_301_redirect_loop_max_redirects', 3))));
	 //      }
	 //    }
	 //  }
  // }
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $userInputValues = $form_state->getUserInput();
    $config = $this->configFactory->getEditable('domain_301_redirect.settings')
      ->set('domain_301_redirect_enabled', $userInputValues['domain_301_redirect_enabled'])
      ->set('domain_301_redirect_domain', $userInputValues['domain_301_redirect_domain'])
      ->set('domain_301_redirect_check_period', $userInputValues['domain_301_redirect_check_period'])
      ->set('domain_301_redirect_domain_check_retries', $userInputValues['domain_301_redirect_domain_check_retries'])
      ->set('domain_301_redirect_domain_check_reenable', $userInputValues['domain_301_redirect_domain_check_reenable'])
      ->set('domain_301_redirect_applicability', $userInputValues['domain_301_redirect_applicability'])
      ->set('domain_301_redirect_pages', $userInputValues['domain_301_redirect_pages'])
      ->save();

    parent::submitForm($form, $form_state);
  }
}
