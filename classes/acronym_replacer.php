<?php
/**
 * Plugin initialisation
 *
 * Initialize the plugin
 * PHP version 5.3
 *
 * @category PHP
 * @package WordPress
 * @subpackage RalfAlbert\AcronymReplacer
 * @author Ralf Albert <me@neun12.de>
 * @license GPLv3 http://www.gnu.org/licenses/gpl-3.0.txt
 * @version 1.0
 * @link http://wordpress.com
 */

namespace RalfAlbert\AcronymReplacer;

use RalfAlbert\Tooling\Autoload\Autoload;
use RalfAlbert\Tooling\Lists\Compact_KeyValue_List;
use RalfAlbert\AcronymReplacer\Backend\MenuItem;
use RalfAlbert\AcronymReplacer\Backend\Replacer;

add_action(
	'init',
	__NAMESPACE__ . '\plugin_init',
	10,
	0
);

function plugin_init() {

	if ( ! is_admin() )
		return false;

	require_once 'autoload.php';

	new Autoload( dirname( __FILE__ ) );

	/*
	 * This class can automatically create the JS and stylesheet for the list on the fly
	 * It expect three params:
	 *  - the slug to enqueue JS and CSS
	 *  - the css-class to be used inside the list (and the JS depends on)
	 *  - the pageslug where the list should be displayed
	 *
	 *  This static call is needed to enqueue the JS and CSS
	 */
	Compact_KeyValue_List::enqueue_scripts( 'acronym-replacer', 'options-writing.php' );

	add_action( 'content_save_pre', array( 'RalfAlbert\AcronymReplacer\Backend\Replacer', 'replace' ), 10, 1 );

	$menuitem = new MenuItem();

}
