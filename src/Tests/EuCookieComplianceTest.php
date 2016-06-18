<?php

namespace Drupal\eu_cookie_compliance\Tests;

/**
 * Test functionality for EU Cookie Compliance.
 *
 * @group eu_cookie_compliance
 */
class EuCookieComplianceTest extends EuCookieComplianceTestBasic {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'locale',
    'eu_cookie_compliance',
    'eu_cookie_compliance_test'
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
  }

  /**
   * Test tour functionality.
   */
  public function testEuCookieComplianceFunctionality() {
    $this->drupalGet('');

    $this->assertEuCookieCompliance();

  }

}
