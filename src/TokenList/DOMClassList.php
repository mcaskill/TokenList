<?php

/**
 * File: DOMClassList
 *
 * @package  XMLManipulation\DOM
 *
 * @license  MIT
 * @author   Chauncey McAskill <chauncey@mcaskill.ca>
 * @version  2016-02-24
 * @since    Version 2015-07-15
 */

namespace McAskill\TokenList;

/**
 * Class: DOMClassList
 *
 * The DOMClassList class is a DOMTokenList representing
 * the HTML "class" attribute of an element.
 *
 * Equivalent to `DOMTokenList( $element, 'class' [, $tokens = [] ] )`.
 *
 * @see DOMRelList for an equivalent to the HTML 'rel' attribute.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/API/Element/classList
 *       The DOMClassList class is a PHP implementation of the JavaScript interface.
 */
class DOMClassList extends DOMTokenList
{
	/**
	 * Retrieve a new DOMClassList object
	 *
	 * @param string|DOMElement         $element The represented HTML element.
	 * @param int|string|(int|string)[] $tokens  One or more tokens to start off the set.
	 *                                           Strings will be split by U+0020.
	 */
	public function __construct( $element, $tokens = [] )
	{
		parent::__construct( $element, 'class', $tokens );
	}
}
