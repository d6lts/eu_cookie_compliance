<?php
/**
 * @file
 * Hooks specific to the EU Cookie Compliance module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter the geo_ip_match variable.
 *
 * @param boolean &$geoip_match
 *   Whether to show the cookie compliance banner.
 */
function hook_eu_cookie_compliance_geoip_match_alter(&$geoip_match) {
  $geoip_match = FALSE;
}

/**
 * @} End of "addtogroup hooks".
 */
