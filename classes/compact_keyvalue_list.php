<?php
/**
 * Compact_KeyValue_List
 *
 * Class to create a simple list from key-value pairs
 * PHP version 5.3
 *
 * @category PHP
 * @package WordPress
 * @subpackage RalfAlbert\Tooling\Lists
 * @author Ralf Albert <me@neun12.de>
 * @license GPLv3 http://www.gnu.org/licenses/gpl-3.0.txt
 * @version 1.0
 * @link http://wordpress.com
 */

namespace RalfAlbert\View\Lists;

/**
 * This will output the js and stylesheet when the file is called with the GET param 'ver'
 */
$what  = filter_input( INPUT_GET, 'ver', FILTER_SANITIZE_STRING );

if ( ! empty( $what ) ) {

	switch ( $what ) {
	 case 'JS':
	 	header( 'Content-Type: application/javascript' );
	 	echo Compact_KeyValue_List::get_js();
	 	exit;
	 break;

	 case 'CSS':
	 	header( 'Content-type: text/css' );
	 	echo Compact_KeyValue_List::get_stylesheet();
	 	exit;
	 break;

	 default:
	 	// nothing to do
	 break;

	}

}

class Compact_KeyValue_List
{

	/**
	 * Array or object with elements to display inside the list.
	 * Must be in format key => value
	 * @var array|object
	 */
	protected $elements = null;

	/**
	 * Name of the option to use
	 * @var string
	 */
	public $option_name = '';

	/**
	 * Slug to enqueue the JS and stylesheet
	 * @var string
	 */
	public $scripts_slug = 'CompactKeyValueList';

	/**
	 * Array for buttons "Add item" and "Delete item"
	 * Array in form 'add' => [button to add items], 'del' => [button to delete items]
	 * @var array
	 */
	public $buttons = array(
			'add' => array( 'text' => 'Add Item', 'type' => 'primary' ),
			'del' => array( 'text' => 'Del Item', 'type' => 'primary' )
	);

	/**
	 * Setup the configuration
	 *
	 * The configuration have to be in format:
	 * array(
	 *   'option_name' => [string options-name],
	 *   'buttons'     => array(
	 *      'add' => [string|array],
	 *      'del' => [string|array]
	 *   )
	 * )
	 *
	 * The values of th keys 'add' and 'del' can be a string or array. If it is a string, the string will be used as
	 * text for the button and the button-type will be 'primary large'. If the values (or one of them) are an array,
	 * then the array can contain the keys 'text' and 'type'. Where 'text' defines the text for the button and 'type'
	 * the type of the button. The button-type can be one of 'primary', 'secondary' or 'delete'. The type can be extended
	 * by 'large' (space seperated; e.g. 'delete large') for older admin-styles.
	 *
	 * @see http://codex.wordpress.org/Function_Reference/get_submit_button
	 * @param	array					$elements	Array with elements for the list in format key => value
	 * @param	array|object	$config		Configuration with css_class, js_slug, css_slug, etc
	 */
	public function __construct( $elements = null, $config = null ) {

		if ( ! empty( $config ) )
			$this->setup_config( $config );

		if ( ! empty( $elements ) )
			$this->elements = $elements;

	}

	/**
	 * Setup the class configuration
	 * @param	array|object	$config
	 */
	public function setup_config( $config ) {

		if ( is_object( $config ) )
			$config = (array) $config;

		$config = (object) array_merge( $this->get_default_config(), $config );

		$this->config_buttons( $config->buttons );

		if ( ! empty( $config->option_name ) )
			$this->option_name = $config->option_name;

		if ( ! empty( $config->slug ) )
			$this->scripts_slug = $config->slug;

	}

	/**
	 * Configure the buttons
	 * @param	array|object	$buttons
	 */
	protected function config_buttons( $buttons ) {

		if ( is_object( $buttons ) )
			$buttons = (array) $buttons;

		foreach ( $buttons as $button => $settings ) {

			// shortcut to compress code
			$current = &$this->buttons[ $button ];

			if ( is_string( $settings ) ) {
				$current['text'] = esc_html( $settings );
			}
			elseif ( is_array( $settings ) || is_object( $settings ) ) {
				$current = $this->set_button_details( $current, (array) $settings );
			}

		}

	}

	/**
	 * Set the details of a button
	 * @param		array		$current	Default settings
	 * @param		array		$settings	Settings to apply
	 * @return	array		$current	Array with valid settings for a button
	 */
	protected function set_button_details( $current, $settings ) {

		$count = 0;

		foreach ( $current as $key => $value ) {

			if ( key_exists( $key, $settings ) )
				$current[ $key ] = $value;
			else
				$current[ $key ] = $settings[ $count++ ];

		}

		return $current;

	}

	/**
	 * Return the default configuration
	 * @return	array	void
	 */
	public function get_default_config() {

		return array(
		 	'option_name' => '',
			'buttons'     => $this->buttons,
			'slug'        => $this->scripts_slug,
		);

	}

	/**
	 * Return the HTML for the list
	 * @param		array|object $elements The elements to display
	 * @return	string
	 */
	public function get_list( $elements = null, $id = '' ) {

		if ( empty( $elements ) ) {

			if ( ! empty( $this->elements ) )
				$elements = $this->elements;
			else
				$elements = array( '' => '' );

		}

		$elements = (array) $elements;

		if ( empty( $id ) )
			$id = $this->option_name;

		// add css-class for jQuery
		$add_text = esc_html( $this->buttons['add']['text'] );
		$add_type = $this->buttons['add']['type'] . ' ckvl-add';

		$del_text = esc_html( $this->buttons['del']['text'] );
		$del_type = $this->buttons['del']['type'] . ' ckvl-del';

		$add_button = get_submit_button( $add_text, $add_type, $id . '-add', false );
		$del_button = get_submit_button( $del_text, $del_type, $id . '-del', false );

		$output =  $inner  = $list =  $buttons = '';

		$wrap_template  = '<div class="wrap ckvl-wrap" id="%s">%s%s<br style="clear:both" /></div>';
		$outer_template = '<div class="ckvl-list">%s</div>';
		$inner_template =

<<<'TEMPL'
<div class="ckvl-line">
	<input type="text" class="ckvl-left" name="%1$s[left][]" value="%2$s" />
	<input type="text" class="ckvl-right" name="%1$s[right][]"  value="%3$s" />
	<br>
</div>
TEMPL;

		foreach ( $elements as $short => $long )
			$inner .= sprintf(
					$inner_template,
					$this->option_name,
					esc_html( $short ),
					esc_html( $long )
			);

		$inner = apply_filters( 'compact_keyvalue_list-inner_list', $inner );

		$list = sprintf(
			$outer_template,
			$inner
		);

		$list = apply_filters( 'compact_keyvalue_list-outer_list', $list );

		$buttons = sprintf(
				'<div class="ckvl-meta"><p>%s%s</p></div>',
				$add_button,
				$del_button
		);

		$buttons = apply_filters( 'compact_keyvalue_list-list_buttons', $buttons );
		$output  = apply_filters( 'compact_keyvalue_list-complete_list', sprintf( $wrap_template, $id, $list, $buttons ) );

		return $output;

	}

	/**
	 * Add the hook on pageload to automatically enqueue the JS and CSS
	 * @param	string	$slug			The script slug used in wp_enqueue_script() and wp_enqueue_style()
	 * @param	string	$class		The CSS class to use
	 * @param	string	$pageslug	Optional: The pageslug where the list will be displayed
	 */
	public static function enqueue_scripts( $slug, $pageslug = 'options-writing.php' ) {

		$this->scripts_slug = $slug;
		add_action( "load-{$pageslug}", array( __CLASS__, '_enqueue_scripts' ), 10, 0 );

	}

	/**
	 * Callback for the automatically script enqueueing
	 */
	public static function _enqueue_scripts() {

		wp_enqueue_script(
			$this->scripts_slug,
			plugin_dir_url( __FILE__ ) . basename( __FILE__ ),
			array( 'jquery' ),
			"JS",
			true
		);

		wp_enqueue_style(
			$this->scripts_slug,
			plugin_dir_url( __FILE__ ) . basename( __FILE__ ),
			false,
			"CSS",
			'all'
		);

	}

	/**
	 * Returns the needed JS for the list with the given css-class
	 * @param		string 					$class	CSS class to use
	 * @return	boolean|string	void		False if no class was set, else the JS
	 */
	public static function get_js() {

		$comp_js =
<<<CompJS
jQuery(document).ready(function(e){e(document.body).on("keypress","ckvl-wrap",function(e){if(e.which==13){e.preventDefault();return false}});e(document.body).on("focus",".ckvl-left, .ckvl-right",function(){var t=e(this).parent().parent("div");t.find("div").each(function(t,n){e(n).removeClass("ckvl-active").css("backgroundColor","#fff")});e(this).parent("div").addClass("ckvl-active").css("backgroundColor","#ddd")});e(".ckvl-del").on("click",function(t){t.preventDefault();list=e(this).parent().parent().prev(".ckvl-list");list.find("div").each(function(t,n){var r=e(n).hasClass("ckvl-active");if(true==r){e(n).remove()}})});e(".ckvl-add").on("click",function(t){t.preventDefault();list=e(this).parent().parent().prev(".ckvl-list");list.find("div").each(function(t,n){e(n).removeClass("ckvl-active").css("backgroundColor","#fff")});lastline=list.find("div").last();newline=lastline.clone();newline.addClass("ckvl-active").css("backgroundColor","#fff");newline.find("input").each(function(t,n){e(n).val("")});lastline.after(newline)})})
CompJS;

 		$js =
<<<JS
jQuery(document).ready(
function($) {

	$(document.body).on(
		'keypress',
		'ckvl-wrap',
		function(e) {
		if (e.which == 13) {
			e.preventDefault();
			return false;
		}
	});

	$(document.body).on(
			'focus',
			'.ckvl-left, .ckvl-right',
			function() {

				var list = $(this).parent().parent('div');

				list.find('div').each( function(i, e) {
					$(e).removeClass( 'ckvl-active' ).css( 'backgroundColor', '#fff' );
				});

				$(this).parent('div').addClass( 'ckvl-active' ).css( 'backgroundColor', '#ddd' );

			});

	$('.ckvl-del').on('click', function(e) {
		e.preventDefault();
		list = $(this).parent().parent().prev('.ckvl-list');

		list.find('div').each(function(i, e) {
			var is_active = $(e).hasClass('ckvl-active');
			if (true == is_active) {
				$(e).remove();
			}
		});


	});

	$('.ckvl-add').on('click', function(e) {
		e.preventDefault();
		list = $(this).parent().parent().prev('.ckvl-list');

		list.find('div').each(function(i, e) {
			$(e).removeClass('ckvl-active').css('backgroundColor', '#fff');
		});

		lastline = list.find('div').last();
		newline = lastline.clone();
		newline.addClass('ckvl-active').css('backgroundColor', '#fff');
		newline.find('input').each(function(i,e){ $(e).val(''); } );
		lastline.after( newline );
	});

});
JS;

		return ( defined( 'SCRIPT_DEBUG' ) && true == SCRIPT_DEBUG ) ?
			$js : $comp_js;

	}

	/**
	 * Return the needed CSS for the list with the given css-class
	 * @param		string 					$class	CSS class to use
	 * @return	boolean|string	void		False if no class was set, else the CSS
	 */
	public static function get_stylesheet() {

		$comp_css =
<<<CompCSS
.ckvl-meta p input{float:right}.ckvl-meta p input:first-child{float:left}.ckvl-list{height:10em;overflow-y:scroll;overflow-x:hidden;border:1px solid #aaa;padding:0;margin:0}.ckvl-meta,.ckvl-list{max-width:39.9em}noindex:-o-prefocus,.ckvl-list{max-width:39.7em}.ckvl-line{background-color:#fff}.ckvl-left,.ckvl-right{display:inline-block !important;padding:0 0 .1em .1em !important;margin:0 0 0 0 !important;text-align:left !important;line-height:1.5em !important;height:1.5em !important;border:0 !important;border-bottom:1px solid #aaa !important;border-right:1px solid #aaa !important;background-color:transparent !important;outline:0}.ckvl-left{width:10em !important}.ckvl-right{width:28.4em !important;margin-left:-0.25em !important}.ckvl-active{background-color:#ddd}
CompCSS;

		$css =
<<<CSS
.ckvl-meta p input {
	float:right;
}

.ckvl-meta p input:first-child {
	float:left;
}

.ckvl-list {
	height: 10em;
	overflow-y: scroll;
	overflow-x: hidden;
	border: 1px solid #aaa;
	padding: 0;
	margin: 0;
}

.ckvl-meta, .ckvl-list {
	max-width: 39.9em;
}

noindex:-o-prefocus, .ckvl-list {
  max-width: 39.7em;
}

.ckvl-line {
	background-color: #fff;
}

.ckvl-left, .ckvl-right {
	display:inline-block !important;
	padding: 0 0 0.1em 0.1em !important;
	margin: 0 0 0 0 !important;
	text-align: left !important;
	line-height: 1.5em !important;
	height: 1.5em !important;
	border: 0 !important;
	border-bottom: 1px solid #aaa !important;
	border-right: 1px solid #aaa !important;
	background-color: transparent !important;
	outline: none;
}

.ckvl-left {
	width: 10em !important;
}

.ckvl-right {
	width: 28.4em !important;
	margin-left: -0.25em !important;
}

.ckvl-active {
	background-color: #ddd;
}
CSS;

		return ( defined( 'SCRIPT_DEBUG' ) && true == SCRIPT_DEBUG ) ?
			$css : $comp_css;

	}

}