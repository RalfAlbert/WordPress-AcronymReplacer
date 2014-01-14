<?php
/**
 * MenuItem
 *
 * Class to create a new menu item in the options menu / submenu writing
 * PHP version 5.3
 *
 * @category PHP
 * @package WordPress
 * @subpackage RalfAlbert\AcronymReplacer\Backend
 * @author Ralf Albert <me@neun12.de>
 * @license GPLv3 http://www.gnu.org/licenses/gpl-3.0.txt
 * @version 1.0
 * @link http://wordpress.com
 */

namespace RalfAlbert\AcronymReplacer\Backend;

use RalfAlbert\Tooling\Lists\Compact_KeyValue_List;

class MenuItem extends \AcronymReplacer_Base
{
	/**
	 * Option Name
	 * @var string
	 */
	public $option_name = '';

	/**
	 * Option Group
	 * @var string
	 */
	public $option_group = '';

	/**
	 * Callback to validate and sanitize the saved options
	 * @var array|string
	 */
	public $validate_callback;

	/**
	 * Set up the class properties and initialize the settings api
	 */
	public function __construct() {

		$this->option_group = 'writing';
		$this->option_name  = self::OPTION_KEY;

		$this->validate_callback = array ( $this, 'validate_options' );

		$this->menu_title = $this->page_title = __( 'Acronym Replacer', self::TEXTDOMAIN );

		add_filter( 'admin_init' , array( $this , 'settings_api_init' ), 10, 0 );

	}

	/**
	 * Initialize the settings api
	 */
	public function settings_api_init() {

		add_settings_section(
			'acronym-replacer-section',
			__( 'Acronym Replacer', self::TEXTDOMAIN ),
			array( $this, 'section_content'),
			$this->option_group
		);

		add_settings_field(
			$this->option_name,
			__( 'Your Acronyms' , self::TEXTDOMAIN ),
			array( $this, 'field_content'),
			$this->option_group,
			'acronym-replacer-section'
		);

		register_setting(
			$this->option_group,
			$this->option_name,
			$this->validate_callback
		);

	}

	/**
	 * Validate and sanitize the saved options
	 *
	 * @param		array						$input	Array with options
	 * @return	boolean|array		$input	False if the hook does not match, else an array with validated and sanitized options
	 */
	public function validate_options( $input ) {

		// check if the right filter is set
		global $wp_current_filter;

		$wp_current_filter = (array) $wp_current_filter;
		$needed_filter     = 'sanitize_option_' . $this->option_name;

		if ( ! isset( $wp_current_filter ) || ! in_array( $needed_filter, $wp_current_filter ) )
			return false;

		if ( isset( $input['left'] ) && isset( $input['right'] ) ) {

			array_walk(
				$keys,
				function ( $key)  use( &$input ) {
					array_walk(
						$input[ $key ],
						function( &$val ){
							$val = esc_html( $val );
						}
					);
				}
			);

			$input = array_combine( $input['left'] , $input['right'] );

			// remove entries with empty key
			array_walk(
				$input,
				function( $val, $key ) use( &$input ){
					if( empty( $key ) || empty( $val ) )
						unset( $input[ $key ] );
				}
			);

		}

		return $input;

	}

	/**
	 * Output the content for the section
	 */
	public function section_content() {

		printf(
			 '<p>%s<br>%s<br>%s<br>%s</p>',
			 esc_html( __( "To edit an acronym, click on it and edit it. You can either click on the short or long form.", self::TEXTDOMAIN ) ),
			 esc_html( __( "To delete an acronym, select it by clicking either on the short or long form and click 'Del Acronym'. The highlighted line will be removed.", self::TEXTDOMAIN ) ),
			 esc_html( __( "All changes will be saved when the 'Update' button is pressed. If you reload the page before pressing the 'Update' button, all changes are reverted.", self::TEXTDOMAIN ) ),
			 esc_html( __( "To add an acronym, just click on the 'Add Acronym' button. A blank line will appear at the end of the list.", self::TEXTDOMAIN ) )
		);

		printf(
			'<p>%s</p>',
			esc_html( __( "The enter key is disabled while you edit an acronym.", self::TEXTDOMAIN ) )
		);

	}

	/**
	 * Output the content for the field
	 */
	public function field_content() {

		$elements = get_option( self::OPTION_KEY );

		$config = array(
		 	'option_name' => $this->option_name,
			'buttons'     => array(
					'add' => __( 'Add Acronym', self::TEXTDOMAIN ),
					'del' => array( __( 'Del Acronym', self::TEXTDOMAIN ), 'delete' )
			)
		);

		$list = new Compact_KeyValue_List( $elements, $config );

		echo $list->get_list();

	}

}