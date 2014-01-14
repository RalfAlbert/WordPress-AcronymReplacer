<?php
/**
 * Simple replacer function, just for testing
 *
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

class Replacer extends \AcronymReplacer_Base
{

	public static function replace( $content ) {

		if ( empty( $content ) )
			return $content;

		$acronymes = get_option( self::OPTION_KEY );

		if ( ! is_array( $acronymes ) || empty( $acronymes ) )
			return $content;

		$pattern     = '#(%s)#isu';
		$replacement = '<acronym title="%s">$1</acronym>';

		foreach ( $acronymes as $short => $long ) {
			$content = preg_replace(
				sprintf( $pattern, $short ),
				sprintf( $replacement, $long ),
				$content
			);
		}

		return $content;

	}

}