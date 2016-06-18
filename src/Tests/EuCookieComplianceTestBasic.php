<?php

namespace Drupal\eu_cookie_compliance\Tests;

/**
 * Simple EUCC test base.
 */
abstract class EuCookieComplianceTestBasic extends EuCookieComplianceTestBase {

  /**
   * An admin user with administrative permissions for EUCC.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * The permissions required for a logged in user to test EUCC.
   *
   * @var array
   *   A list of permissions.
   */
  protected $permissions = array('Administer EU Cookie Compliance popup');

  protected function setUp() {
    parent::setUp();

    // Make sure we are using distinct default and administrative themes for
    // the duration of these tests.
    $this->container->get('theme_handler')->install(array('bartik', 'seven'));
    $this->config('system.theme')
      ->set('default', 'bartik')
      ->set('admin', 'seven')
      ->save();

    $this->permissions[] = 'view the administration theme';

    // Create an admin user to view tour tips.
    $this->adminUser = $this->drupalCreateUser($this->permissions);
    $this->drupalLogin($this->adminUser);
  }

}
