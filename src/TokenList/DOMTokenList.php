<?php

/**
 * File: DOMTokenList
 *
 * @package  XMLManipulation\DOM
 *
 * @license  MIT
 * @author   Chauncey McAskill <chauncey@mcaskill.ca>
 * @version  2016-02-24
 * @since    Version 2015-07-15
 */

namespace McAskill\TokenList;

use DOMNode;
use DOMAttr;
use DOMElement;
use DOMException;
use InvalidArgumentException;

/**
 * Class: DOMTokenList
 *
 * The DOMTokenList class is a StringTokenList representing an
 * HTML attribute of an element.
 *
 * In contrast to its JavaScript equivalent, the class provides methods
 * for returning a string representation of an HTML attribute and value.
 *
 * Such a set is extended by DOMClassList and DOMRefList.
 *
 * **Example #1 Basic Usage**
 *
 * ```php
 * <?php
 *
 * $body_tags = new DOMTokenList( 'body', 'data-tags', [ 'foo', 'baz', 'qux' ] );
 *
 * var_dump( $body_tags->__toAttribute() );
 * var_dump( $body_tags->attr() );
 * ```
 *
 * The above example will output something similar to:
 *
 * ```
 * string(23) 'data-tags="foo baz qux"'
 * string(24) ' data-tags="foo baz qux"' // Take note of the preceding space
 * ```
 *
 * **Example #2 XML Manipulation Usage**
 *
 * When passing a DOMElement to DOMTokenList, the instance will
 * select the DOMAttr for the 'class' attribute. This will keep
 * the value of the attribute in the DOMNode up to date.
 *
 * ```php
 * <?php
 *
 * $document = new DOMDocument;
 * $document->loadhtmlfile('...');
 *
 * $body = $document->getElementsByTagName('body');
 * $body = $body->item(0);
 *
 * $body_classes = new DOMTokenList( $body, 'class' );
 * ```
 *
 * @property-read int $length Calls StringTokenList::count()
 */
class DOMTokenList extends StringTokenList
{

// Properties
// ----------------------------------------------------------------------------

	/**
	 * A reference to the HTML element that DOMTokenList represents.
	 *
	 * @var string|DOMElement
	 */
	private $element;

	/**
	 * A reference to the HTML attribute that DOMTokenList represents.
	 *
	 * @var string|DOMAttr
	 */
	private $attribute;



// Methods
// ----------------------------------------------------------------------------

	/**
	 * Retrieve a new DOMTokenList object
	 *
	 * @param string|DOMElement         $element   The represented HTML element.
	 * @param string|DOMAttr            $attribute The represented HTML attribute.
	 * @param int|string|(int|string)[] $tokens    One or more tokens to start off the set.
	 *                                             Strings will be split by U+0020.
	 *
	 * @throws InvalidArgumentException If the provided $element is not a string
	 *                                  or a DOMNode with attribute methods.
	 * @throws InvalidArgumentException If the provided $attribute is not a string
	 *                                  or a DOMAttr.
	 * @throws DOMException             If $element is a DOMElement object and the
	 *                                  provided $attribute is not accesible.
	 * @throws InvalidArgumentException If the provided $tokens is not a scalar
	 *                                  or an array of.
	 */
	public function __construct( $element, $attribute, $tokens = [] )
	{
		$is_elem_string = is_string( $element );
		$is_DOMElement  = ( $element instanceof DOMElement );

		if ( ! $is_elem_string && ! $is_DOMElement ) {
			throw new InvalidArgumentException(
				'Invalid value for $element. Must be a string or a DOMElement-like object.'
			);
		}

		$is_attr_string = is_string( $attribute );
		$is_DOMAttr     = ( $attribute instanceof DOMAttr );

		if ( ! $is_attr_string && ! $is_DOMAttr ) {
			throw new InvalidArgumentException(
				'Invalid value for $attribute. Must be a string or a DOMAttr object.'
			);
		}

		if ( $is_DOMElement ) {
			if ( $is_DOMAttr && $attribute->ownerElement !== $element ) {
				throw new DOMException(
					'The $attribute (DOMAttr) does not belong to the $element (DOMElement).'
				);
			}
			elseif ( $is_attr_string && ( $__attr = $element->getAttributeNode( $attribute ) ) ) {
				$attribute  = $__attr;
				$is_DOMAttr = true;
			}
			else {
				throw new DOMException(
					'The $attribute (DOMAttr) does not exist in the $element (DOMElement).'
				);
			}
		}

		$this->element   = $element;
		$this->attribute = $attribute;

		if ( $is_DOMAttr && $attribute->value ) {
			$__tokens = preg_split( '/\s+/', $attribute->value );
			$tokens   = array_merge( $__tokens, $tokens );
		}

		parent::__construct( $tokens );
	}

	/**
	 * Dynamically retrieve a value from the DOMTokenList.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function __get( $name )
	{
		switch ( $name ) {
			case 'length':
				return $this->count();
				break;

			default:
				return parent::__get( $name );
				break;
		}
	}

	/**
	 * Updates the HTML attribute that DOMTokenList represents
	 *
	 * Will only update the value if self::$attribute is a DOMAttr.
	 */
	protected function __update()
	{
		/** Rebase array indices */
		parent::__update();

		if ( $this->count() && $this->attribute instanceof DOMAttr ) {
			$this->attribute->value = (string) $this;
		}
	}

	/**
	 * Retrieves the HTML attribute and concatenated list of the tokens.
	 *
	 * @return string Stringified token list, wrapped in an HTML attribute.
	 */
	public function __toAttribute()
	{
		if ( $this->count() ) {
			$attr = null;

			if ( is_string( $this->attribute ) ) {
				$attr = $this->attribute;
			}
			elseif ( $this->attribute instanceof DOMAttr ) {
				$attr = $this->attribute->name;
			}

			if ( $attr ) {
				$values = htmlspecialchars((string) $this, ENT_QUOTES, ini_get('default_charset'), false);
				return sprintf('%1$s="%2$s"', $attr, $values);
			}
		}

		return '';
	}

	/**
	 * Equivalent to __toAttribute()
	 *
	 * Prepends a space before the attribute declaration.
	 *
	 * @return string
	 */
	public function attr()
	{
		$attr = $this->__toAttribute();

		if ( $attr ) {
			return ' ' . $attr;
		}
	}

}
