<?php

/**
 * File: DOMRelList
 *
 * @package  XMLManipulation\DOM
 *
 * @license  MIT
 * @author   Chauncey McAskill <chauncey@mcaskill.ca>
 * @version  2015-07-16
 * @since    Version 2015-07-15
 */

namespace McAskill\TokenList;

/**
 * Class: DOMRelList
 *
 * The DOMRelList class is a DOMTokenList representing
 * the HTML "class" attribute of an element.
 *
 * Equivalent to `DOMTokenList( $element, 'rel' [, $tokens = [] ] )`.
 *
 * @see DOMClassList for an equivalent to the HTML 'class' attribute.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/API/HTMLLinkElement/relList
 *       The DOMRelList class is a PHP implementation of the JavaScript interface.
 */

class DOMRelList extends DOMTokenList
{

// Methods
// ----------------------------------------------------------------------------

	/**
	 * Retrieve a new DOMRelList object
	 *
	 * @param string|DOMElement         $element The represented HTML element.
	 * @param int|string|(int|string)[] $tokens  One or more tokens to start off the set.
	 *                                           Strings will be split by U+0020.
	 *
	 * @return $this
	 */

	public function __construct( $element, $tokens = [] )
	{
		parent::__construct( $element, 'rel', $tokens );

		return $this;
	}

}
