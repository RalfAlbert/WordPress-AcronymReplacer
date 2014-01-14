<?php
/**
 * Plugin Name:	Acronym Replacer
 * Plugin URI:
 * Text Domain: acronym_replacer
 * Domain Path: /languages
 * Description:	Replaces acronyms. Plugin for testing the Compact_KeyValue_List class.
 * Author:      Ralf Albert
 * Author URI:  http://yoda.neun12.de/
 * Version:     1.0
 * License:     GPLv3
 */

! ( defined( 'ABSPATH' ) ) AND die( 'Standing On The Shoulders Of Giants' );

if ( version_compare( phpversion(), '5.3', '<' ) )
	wp_die( 'This plugin requires PHP5.3.' );

class AcronymReplacer_Base
{
	/**
	 * Key for the data in the options table
	 * @var string
	 */
	const OPTION_KEY = 'acronymreplacer';

	/**
	 * Constant for Textdomain
	 * @var string
	 */
	const TEXTDOMAIN = 'acronym_replacer';
}

register_activation_hook( __FILE__, 'acronym_replacer_activation' );

function acronym_replacer_activation() {

	if ( version_compare( phpversion(), '5.3', '<' ) )
		die(
				sprintf(
					'<p>%s <strong>5.3</strong>.</p>',
					esc_html( __( 'This plugin requires PHP', AcronymReplacer_Base::TEXTDOMAIN ) )
				)
		);

	add_option( AcronymReplacer_Base::OPTION_KEY, array() );

}

/*
 * Just for testing to delete the options on deactivation!
 */
register_deactivation_hook( __FILE__,  'acronym_replacer_uninstall' );

register_uninstall_hook( __FILE__,  'acronym_replacer_uninstall' );

function acronym_replacer_uninstall() {

	delete_option( AcronymReplacer_Base::OPTION_KEY );

}

require_once 'classes/acronym_replacer.php';
