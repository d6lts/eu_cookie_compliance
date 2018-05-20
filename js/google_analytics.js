(function ($, Drupal, drupalSettings) {
  if (drupalSettings.eu_cookie_compliance.GA_id && !Drupal.eu_cookie_compliance.hasAgreed()) {
    window['ga-disable-' + drupalSettings.eu_cookie_compliance.GA_id] = true;
  }
})(jQuery, Drupal, drupalSettings);
