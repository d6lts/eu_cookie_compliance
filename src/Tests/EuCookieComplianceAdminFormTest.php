<?php

namespace Drupal\eu_cookie_compliance\Tests;

/**
 * Test functionality for EU Cookie Compliance Admin Config form.
 *
 * @group eu_cookie_compliance
 */
class EuCookieComplianceAdminFormTest extends EuCookieComplianceTestBasic {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'eu_cookie_compliance',
    'eu_cookie_compliance_test'
  ];

  /**
   * Test routes.
   */
  public function testRoutes() {
    $this->drupalLogin($this->adminUser);

    $this->drupalGet('admin/config/system/eu-cookie-compliance');
    $this->assertResponse(200);

    $this->assertText('EU Cookie Compliance', 'Right Text');
  }

  /**
   * Tests the admin UI.
   */
  public function testAdminUI() {
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/config/system/eu-cookie-compliance');
    $this->assertNoFieldChecked('edit-popup-enabled');
    $edit = ['popup_enabled' => 1];

    $this->drupalPostForm(NULL, $edit, 'Save configuration');
    $this->assertFieldChecked('edit-popup-enabled');
    $this->assertText('Privacy policy link field is required.');

    $edit += ['popup_link' => 'https://drupal.org'];
    $this->drupalPostForm(NULL, $edit, 'Save configuration');
    $this->assertText('The configuration options have been saved.');
  }

}
