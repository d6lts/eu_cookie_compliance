<?php

namespace Drupal\eu_cookie_compliance\Tests;

use Drupal\Core\Url;

/**
 * Test functionality for EU Cookie Compliance Config form.
 *
 * @group eu_cookie_compliance
 */
class EuCookieComplianceConfigFormTest extends EuCookieComplianceTestBase {

  /**
   * An admin user with administrative permissions for EUCC.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'eu_cookie_compliance',
    'eu_cookie_compliance_test',
    'node',
    'path',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    // Create and log in admin user.
    $this->adminUser = $this->drupalCreateUser(['display eu cookie compliance popup', 'administer eu cookie compliance popup', 'access content', 'administer url aliases']);
    $this->drupalCreateContentType(['type' => 'page', 'name' => 'Basic page']);
  }

  /**
   * Tests the EuCookieComplianceConfigForm.
   */
  public function testEuCookieComplianceConfigForm() {
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

  /**
   * Tests the popup link validation and configuration.
   */
  public function testPopupLinks() {
    $this->drupalLogin($this->adminUser);

    // Create 2 nodes.
    $this->createNode();
    $this->createNode();

    // Set a path alias for the second node.
    $this->drupalPostForm('/admin/config/search/path/add', ['source' => '/node/2', 'alias' => '/alias'], t('Save'));

    // Verify that the popup link field is a textfield, since type='url' only
    // accepts absolute urls.
    $this->drupalGet('/admin/config/system/eu-cookie-compliance');
    $this->assertFieldByXpath('//input[@id="edit-popup-link" and @type="text"]');

    $scenarios = [
      // Format: User-entered value, value displayed in form, actual value for link.

      // External URLs.
      ['http://example.com/', 'http://example.com/', 'http://example.com/'],
      ['https://drupal.org', 'https://drupal.org', 'https://drupal.org'],

      // Internal URL without alias.
      ['/node/1', '/node/1', '/node/1'],

      // Internal URL with alias.
      ['/node/2', '/node/2', '/alias'],

      // Internal URL with alias with fragment.
      ['/node/2#anchor', '/node/2#anchor', '/alias#anchor'],

      // Special-case URLs.
      ['<front>', '<front>', '/'],

      // Spaces.
      ['/lorem ipsum', '/lorem ipsum', '/lorem ipsum'],
    ];

    foreach ($scenarios as $scenario) {
      // Configure EU cookie compliance.
      $edit = ['popup_link' => $scenario[0], 'popup_enabled' => TRUE];
      $this->drupalPostForm('/admin/config/system/eu-cookie-compliance', $edit, t('Save configuration'));

      // Verify the popup link input field after submit.
      $this->assertFieldByXpath('//input[@id="edit-popup-link"]', $scenario[1]);

      // Load a page and verify the popup link in the page settings.
      $this->drupalGet('/node/1');
      $settings = $this->getDrupalSettings();
      $expected_url = strpos($scenario[2], '/') === 0 ? Url::fromUserInput($scenario[2])->toString() : $scenario[2];
      $this->assertEqual($settings['eu_cookie_compliance']['popup_link'], $expected_url);
    }

    // Test validation for the popup link setting.
    $invalid_url_scenarios = [
      'node/1' => "The user-entered string &#039;node/1&#039; must begin with a &#039;/&#039;, &#039;?&#039;, or &#039;#&#039;.",
      'ftp://example.com' => "Invalid protocol specified for",
    ];

    foreach ($invalid_url_scenarios as $input => $message) {
      $edit = ['popup_link' => $input];
      $this->drupalPostForm('/admin/config/system/eu-cookie-compliance', $edit, t('Save configuration'));
      $this->assertRaw($message);
    }
  }

}
