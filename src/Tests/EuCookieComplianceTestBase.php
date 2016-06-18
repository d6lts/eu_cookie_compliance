<?php

namespace Drupal\eu_cookie_compliance\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Base class for testing EUCC functionality.
 */
abstract class EuCookieComplianceTestBase extends WebTestBase {

  /**
   * Assert function to determine if EU Cookie Compliance rendered to the page
   * have a corresponding page element.
   *
   * @code
   * // Basic example.
   * $this->assertEuCookieCompliance();
   * @endcode
   */
  public function assertEuCookieCompliance() {

    $rendered_eucc = $this->xpath('//div[@id = "sliding-popup"]//div[starts-with(@class, "popup-content")]');

    $this->assertTrue($rendered_eucc, 'EU Cookie Compliance render.');

    // Get the rendered tips and their data-id and data-class attributes.
    if (empty($tips)) {
      // Tips are rendered as <li> elements inside <ol id="tour">.
      $rendered_tips = $this->xpath('//ol[@id = "tour"]//li[starts-with(@class, "tip")]');
      foreach ($rendered_tips as $rendered_tip) {
        $attributes = (array) $rendered_tip->attributes();
        $tips[] = $attributes['@attributes'];
      }
    }

    // If the tips are still empty we need to fail.
    if (empty($tips)) {
      $this->fail('Could not find tour tips on the current page.');
    }
    else {
      // Check for corresponding page elements.
      $total = 0;
      $modals = 0;
      foreach ($tips as $tip) {
        if (!empty($tip['data-id'])) {
          $elements = \PHPUnit_Util_XML::cssSelect('#' . $tip['data-id'], TRUE, $this->content, TRUE);
          $this->assertTrue(!empty($elements) && count($elements) === 1, format_string('Found corresponding page element for tour tip with id #%data-id', array('%data-id' => $tip['data-id'])));
        }
        elseif (!empty($tip['data-class'])) {
          $elements = \PHPUnit_Util_XML::cssSelect('.' . $tip['data-class'], TRUE, $this->content, TRUE);
          $this->assertFalse(empty($elements), format_string('Found corresponding page element for tour tip with class .%data-class', array('%data-class' => $tip['data-class'])));
        }
        else {
          // It's a modal.
          $modals++;
        }
        $total++;
      }
      $this->pass(format_string('Total %total Tips tested of which %modals modal(s).', array('%total' => $total, '%modals' => $modals)));
    }
  }

}
