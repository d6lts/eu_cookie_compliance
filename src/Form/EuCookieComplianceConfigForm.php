<?php

namespace Drupal\eu_cookie_compliance\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Routing\RequestContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides settings for eu_cookie_compliance module.
 */
class EuCookieComplianceConfigForm extends ConfigFormBase {

  /**
   * The path validator.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * The request context.
   *
   * @var \Drupal\Core\Routing\RequestContext
   */
  protected $requestContext;

  /**
   * Constructs an EuCookieComplianceConfigForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Path\PathValidatorInterface $path_validator
   *   The path validator.
   * @param \Drupal\Core\Routing\RequestContext $request_context
   *   The request context.
   */
  public function __construct(ConfigFactoryInterface $config_factory, PathValidatorInterface $path_validator, RequestContext $request_context) {
    parent::__construct($config_factory);

    $this->pathValidator = $path_validator;
    $this->requestContext = $request_context;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('path.validator'),
      $container->get('router.request_context')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'eu_cookie_compliance_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'eu_cookie_compliance.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('eu_cookie_compliance.settings');

    $default_filter_format = filter_default_format();
    if ($default_filter_format == 'filtered_html') {
      $default_filter_format = 'full_html';
    }

    $form['info'] = array(
      '#markup' => t('<p><strong>Note:</strong> <ul><li>The permission "Display EU Cookie Compliance popup" needs to be enabled for Anonymous and Authenticated users in order for the banner to appear.</li><li>In order for the module to work, <code>js-placeholder</code> needs to be output before <code>js-bottom-placeholder</code> in your <code>html.html.twig</code>.</li></ul></p>'),
    );

    $form['popup_enabled'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable popup'),
      '#default_value' => $config->get('popup_enabled'),
    );

    $form['popup_message'] = array(
      '#type' => 'details',
      '#title' => t('Popup Message'),
      '#open' => TRUE,
    );

    $form['popup_message']['popup_clicking_confirmation'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Consent by clicking'),
      '#default_value' => $config->get('popup_clicking_confirmation'),
      '#description' => $this->t('By default by clicking any link on the website the visitor accepts the cookie policy. Uncheck this box if you do not require this functionality. You may want to edit the pop-up message below accordingly.'),
    );

    $form['popup_message']['popup_info'] = array(
      '#type' => 'text_format',
      '#title' => $this->t('Popup message - requests consent'),
      '#default_value' => $config->get('popup_info.value'),
      '#required' => TRUE,
      '#format' => !empty($config->get('popup_info.format')) ? $config->get('popup_info.format') : $default_filter_format,
    );

    $form['popup_message']['popup_agree_button_message'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Agree button label'),
      '#default_value' => $config->get('popup_agree_button_message'),
      '#size' => 30,
      '#required' => TRUE,
    );

    $form['popup_message']['popup_disagree_button_message'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Disagree button label'),
      '#default_value' => $config->get('popup_disagree_button_message'),
      '#size' => 30,
      '#required' => TRUE,
    );

    $form['thank_you'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Thank You Message'),
    );

    $form['thank_you']['popup_agreed_enabled'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable thank you message'),
      '#default_value' => $config->get('popup_agreed_enabled'),
    );

    $form['thank_you']['popup_hide_agreed'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Clicking hides thank you message'),
      '#default_value' => $config->get('popup_hide_agreed'),
      '#description' => $this->t('Clicking a link hides the thank you message automatically.'),
    );

    $form['thank_you']['popup_agreed'] = array(
      '#type' => 'text_format',
      '#title' => $this->t('Popup message - thank you for giving consent'),
      '#default_value' => !empty($config->get('popup_agreed')['value']) ? $config->get('popup_agreed')['value'] : '',
      '#required' => TRUE,
      '#format' => !empty($config->get('popup_agreed')['format']) ? $config->get('popup_agreed')['format'] : $default_filter_format,
    );

    $form['thank_you']['popup_find_more_button_message'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Find more button label'),
      '#default_value' => $config->get('popup_find_more_button_message'),
      '#size' => 30,
      '#required' => TRUE,
    );

    $form['thank_you']['popup_hide_button_message'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Hide button label'),
      '#default_value' => $config->get('popup_hide_button_message'),
      '#size' => 30,
      '#required' => TRUE,
    );

    $form['privacy'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Privacy Policy'),
    );

    $form['privacy']['popup_link'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Privacy policy link'),
      '#default_value' => $config->get('popup_link'),
      '#maxlength' => 1024,
      '#required' => TRUE,
      '#description' => $this->t('Enter link to your privacy policy or other page that will explain cookies to your users, external links should start with http:// or https://.'),
      '#element_validate' => array(array($this, 'validatePopupLink')),
    );

    $form['privacy']['popup_link_new_window'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Open privacy policy link in a new window'),
      '#default_value' => $config->get('popup_link_new_window'),
    );

    $form['appearance'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Appearance'),
    );

    $form_color_picker_type = 'textfield';

    if (\Drupal::moduleHandler()->moduleExists('jquery_colorpicker')) {
      $form_color_picker_type = 'jquery_colorpicker';
    }

    $form['appearance']['popup_text_hex'] = array(
      '#type' => $form_color_picker_type,
      '#title' => $this->t('Text color'),
      '#default_value' => $config->get('popup_text_hex'),
      '#description' => $this->t('Change the text color of the popup. Provide HEX value without the #.'),
      '#element_validate' => array('eu_cookie_compliance_validate_hex'),
    );

    $form['appearance']['popup_bg_hex'] = array(
      '#type' => $form_color_picker_type,
      '#title' => $this->t('Background color'),
      '#default_value' => $config->get('popup_bg_hex'),
      '#description' => $this->t('Change the background color of the popup. Provide HEX value without the #.'),
      '#element_validate' => array('eu_cookie_compliance_validate_hex'),
    );

    $form['appearance']['popup_height'] = array(
      '#type' => 'number',
      '#title' => $this->t('Popup height in pixels'),
      '#default_value' => !empty($config->get('popup_height')) ? $config->get('popup_height') : '',
      '#field_suffix' => ' ' . $this->t('pixels'),
      '#size' => 5,
      '#maxlength' => 5,
      '#required' => FALSE,
      '#description' => $this->t('Enter an integer value for a desired height in pixels or leave empty for automatically adjusted height.'),
    );

    $form['appearance']['popup_width'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Popup width in pixels or a percentage value'),
      '#default_value' => $config->get('popup_width'),
      '#field_suffix' => ' ' . t('px or %'),
      '#size' => 5,
      '#maxlength' => 5,
      '#required' => TRUE,
      '#description' => $this->t('Set the width of the popup. This can be either an integer value or percentage of the screen width. For example: 200 or 50%.'),
    );

    $form['advanced'] = array(
      '#type' => 'details',
      '#open' => FALSE,
      '#title' => t('Advanced'),
    );

    $form['advanced']['popup_position'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Place the pop-up at the top of the website'),
      '#default_value' => $config->get('popup_position'),
      '#description' => $this->t('By default the pop-up appears at the bottom of the website. Tick this box if you want it to appear at the top.'),
    );

    $form['advanced']['fixed_top_position'] = array(
      '#type' => 'checkbox',
      '#title' => t('If the banner is at the top, don’t scroll the banner with the page'),
      '#default_value' => $config->get('fixed_top_position'),
      '#description' => t('Use position:fixed for the banner when displayed at the top.'),
    );

    $form['advanced']['popup_delay'] = array(
      '#type' => 'number',
      '#title' => $this->t('Popup time delay in seconds'),
      '#default_value' => $config->get('popup_delay'),
      '#field_suffix' => ' ' . t('seconds'),
      '#size' => 5,
      '#maxlength' => 5,
      '#required' => TRUE,
    );

    $form['advanced']['popup_scrolling_confirmation'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Consent by scrolling'),
      '#default_value' => $config->get('popup_scrolling_confirmation'),
      '#description' => $this->t('Scrolling makes the visitors to accept the cookie policy. In some countries, like Italy, it is permitted.'),
    );

    // Adding option to add/remove popup on specified domains
    $exclude_domains_option_active = array(
      0 => $this->t('Add'),
      1 => $this->t('Remove'),
    );

    $form['advanced']['domains_option'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Add/remove popup on specified domains'),
      '#default_value' => $config->get('domains_option'),
      '#options' => $exclude_domains_option_active,
      '#description' => $this->t('Specify if you want to add or remove popup on the listed below domains.'),
    );

    $form['advanced']['domains_list'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Domains list'),
      '#default_value' => $config->get('domains_list'),
      '#description' => $this->t('Specify domains with protocol (e.g., http or https). Enter one domain per line.'),
    );

    $form['advanced']['exclude_paths'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Exclude paths'),
      '#default_value' => !empty($config->get('exclude_paths')) ? $config->get('exclude_paths') : '',
      '#description' => $this->t("Specify pages by using their paths. Enter one path per line. The '*' character is a wildcard. Example paths are %blog for the blog page and %blog-wildcard for every personal blog. %front is the front page.", array(
        '%blog' => '/blog',
        '%blog-wildcard' => '/blog/*',
        '%front' => '<front>'
      )),
    );

    $form['advanced']['domain'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Domain'),
      '#default_value' => $config->get('domain'),
      '#description' => $this->t('Sets the domain of the cookie to a specific url. Used when you need consistency across domains. This is language independent. Note: Make sure you actually enter a domain that the browser can make use of. For example if your site is accessible at both www.domain.com and domain.com, you will not be able to hide the banner at domain.com if your value for this field is www.domain.com.'),
    );

    $form['advanced']['cookie_lifetime'] = array(
      '#type' => 'number',
      '#title' => $this->t('Cookie lifetime'),
      '#description' => $this->t('How long does the system remember the user\'s choice, in days.'),
      '#default_value' => $config->get('cookie_lifetime'),
      '#field_suffix' => ' ' . $this->t('days'),
      '#size' => 5,
      '#maxlength' => 5,
      '#required' => TRUE,
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('eu_cookie_compliance.settings')
      ->set('domain', $form_state->getValue('domain'))
      ->set('popup_enabled', $form_state->getValue('popup_enabled'))
      ->set('popup_clicking_confirmation', $form_state->getValue('popup_clicking_confirmation'))
      ->set('popup_scrolling_confirmation', $form_state->getValue('popup_scrolling_confirmation'))
      ->set('popup_position', $form_state->getValue('popup_position'))
      ->set('popup_agree_button_message', $form_state->getValue('popup_agree_button_message'))
      ->set('popup_disagree_button_message', $form_state->getValue('popup_disagree_button_message'))
      ->set('popup_info', $form_state->getValue('popup_info'))
      ->set('popup_agreed_enabled', $form_state->getValue('popup_agreed_enabled'))
      ->set('popup_hide_agreed', $form_state->getValue('popup_hide_agreed'))
      ->set('popup_find_more_button_message', $form_state->getValue('popup_find_more_button_message'))
      ->set('popup_hide_button_message', $form_state->getValue('popup_hide_button_message'))
      ->set('popup_agreed', $form_state->getValue('popup_agreed'))
      ->set('popup_link', $form_state->getValue('popup_link'))
      ->set('popup_link_new_window', $form_state->getValue('popup_link_new_window'))
      ->set('popup_height', $form_state->getValue('popup_height'))
      ->set('popup_width', $form_state->getValue('popup_width'))
      ->set('popup_delay', $form_state->getValue('popup_delay'))
      ->set('popup_bg_hex', $form_state->getValue('popup_bg_hex'))
      ->set('popup_text_hex', $form_state->getValue('popup_text_hex'))
      ->set('domains_option', $form_state->getValue('domains_option'))
      ->set('domains_list', $form_state->getValue('domains_list'))
      ->set('exclude_paths', $form_state->getValue('exclude_paths'))
      ->set('cookie_lifetime', $form_state->getValue('cookie_lifetime'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Validates the popup link field.
   */
  public function validatePopupLink($element, FormStateInterface &$form_state) {
    if (empty($element['#value'])) {
      return;
    }

    $input = $element['#value'];
    if (UrlHelper::isExternal($input)) {
      $allowed_protocols = ['http', 'https'];
      if (!in_array(parse_url($input, PHP_URL_SCHEME), $allowed_protocols)) {
        $form_state->setError($element, t('Invalid protocol specified for the %name (valid protocols: %protocols).', array('%name' => $element['#title'], '%protocols' => implode(', ', $allowed_protocols))));
      }
      else {
        try {
          Url::fromUri($input);
        }
        catch (\Exception $exc) {
          $form_state->setError($element, t('Invalid %name (:message).', array('%name' => $element['#title'], ':message' => $exc->getMessage())));
        }
      }
    }
    else {
      // Special case for '<front>'.
      if ($input === '<front>') {
        $input = '/';
      }
      try {
        Url::fromUserInput($input);
      }
      catch (\Exception $exc) {
        $form_state->setError($element, t('Invalid URL in %name field (:message).', array('%name' => $element['#title'], ':message' => $exc->getMessage())));
      }
    }
  }

}
