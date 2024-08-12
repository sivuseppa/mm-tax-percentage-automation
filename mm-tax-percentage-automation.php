<?php
/**
 * Plugin Name: MM Change Tax Percentage
 * Description: This plugin changes the tax percentage from 24% to 25.5% on all tax classes on 1.9.2024.
 * Version: 1.0.1
 * Author: Mikko Mörö
 * Author URI:
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: mm-tax-percentage-automation
 * Requires Plugins: woocommerce
 *
 * @package MM Tax Percentage Automation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action( 'init', 'mm_process_tax_classes' );

/**
 * Process tax classes and change the tax percentage if needed.
 */
function mm_process_tax_classes() {

	if ( get_option( 'mm_tax_rate_changed' ) ) {
		return;
	}

	if ( current_datetime()->format( 'Y-m-d' ) > '2024-08-31' ) {

		$logger    = new WC_Logger();
		$tax_rates = array();

		// Get rates for standard tax class.
		$standard_rates = WC_Tax::get_rates_for_tax_class( 0 );
		$tax_rates      = array_merge( $tax_rates, $standard_rates );

		// Get rates for other tax classes.
		$tax_classes = WC_Tax::get_tax_classes();
		foreach ( $tax_classes as $tax_class ) {
			$tax_rates = array_merge( $tax_rates, WC_Tax::get_rates_for_tax_class( $tax_class ) );
		}

		// Change the tax percentage.
		foreach ( $tax_rates as $tax_rate ) {
			$rate_array = WC_Tax::_get_tax_rate( $tax_rate->tax_rate_id );

			if ( '24.0000' === $rate_array['tax_rate'] ) {
				$rate_array['tax_rate']      = '25.5000';
				$rate_array['tax_rate_name'] = 'ALV 25,5%';

				WC_Tax::_update_tax_rate( $rate_array['tax_rate_id'], $rate_array );
				$logger->info( 'Tax percentage changed to 25.5%' );
			}
		}

		update_option( 'mm_tax_rate_changed', 1 );
	}
}
