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
   * An admin user with administrative permissions for EUCC.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $anonUser;

  /**
   * The permissions required for a logged in user to test EUCC.
   *
   * @var array
   *   A list of permissions.
   */
  protected $permissions = ['Administer EU Cookie Compliance popup', 'Display EU Cookie Compliance popup'];

  /**
   * {@inheritdoc}
   */
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

    // Create an admin user to manage EU Cookie Compliance.
    $this->adminUser = $this->drupalCreateUser($this->permissions);

    // Create an anonymous user to view EU Cookie Compliance popup.
    $this->anonUser = $this->drupalCreateUser(['view content', 'Display EU Cookie Compliance popup']);

  }

}
