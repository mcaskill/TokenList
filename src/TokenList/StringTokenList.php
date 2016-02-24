<?php

/**
 * File: StringTokenList
 *
 * @license  MIT
 * @author   Chauncey McAskill <chauncey@mcaskill.ca>
 * @version  2016-02-24
 * @since    Version 2014-06-07
 */

namespace McAskill\TokenList;

use Iterator;
use ArrayAccess;
use Countable;
use InvalidArgumentException;

/**
 * Class: StringTokenList
 *
 * The StringTokenList class represents a set of distinct space-separated tokens.
 * It provides the main functionalities of a PHP Array. The main differences are
 * that the StringTokenList:
 *
 * - allows only integers as indexes,
 * - is always case-sensitive,
 * - and excludes duplicate values.
 *
 * Such a set is extended by DOMTokenList.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/API/DOMTokenList
 *       The StringTokenList class is a PHP implementation of
 *       the DOMTokenList JavaScript interface.
 *
 * Example #1 Basic Usage
 *
 * ```php
 * <?php
 *
 * $obj = new StringTokenList;
 *
 * $obj->add('foo baz qux');
 * var_dump( $obj->value );
 *
 * $obj->add([ 'foo', 'not', 'qux', 'xor' ]);
 * var_dump( $obj->value );
 *
 * $obj->remove([ 'foo', 'qux' ]);
 * var_dump( $obj->value );
 *
 * $obj->replace( 'not', 'and' );
 * var_dump( $obj->value );
 *
 * $obj->toggle( 'foo' );
 * var_dump( $obj->value );
 *
 * $obj->toggle( 'foo' );
 * var_dump( $obj->value );
 *
 * var_dump( $obj->contains('and') );
 *
 * var_dump( $obj->item(1) );
 *
 * var_dump( (string) $obj );
 *
 * var_dump( count( $obj ) ); // Equivalent to $obj->count();
 * ```
 *
 * The above example will output something similar to:
 *
 * ```
 * array (
 *   0 => 'foo',
 *   1 => 'baz',
 *   2 => 'qux',
 * )
 * array (
 *   0 => 'foo',
 *   1 => 'baz',
 *   2 => 'qux',
 *   3 => 'not',
 *   4 => 'xor',
 * )
 * array (
 *   0 => 'baz',
 *   1 => 'not',
 *   2 => 'xor',
 * )
 * array (
 *   0 => 'baz',
 *   1 => 'and',
 *   2 => 'xor',
 * )
 * array (
 *   0 => 'baz',
 *   1 => 'and',
 *   2 => 'xor',
 *   3 => 'foo',
 * )
 * array (
 *   0 => 'baz',
 *   1 => 'and',
 *   2 => 'xor',
 * )
 * bool(true)
 * string(3) "xor"
 * string(11) "baz xor and"
 * int(3)
 * ```
 *
 * Example #2 Syntactic Sugar
 *
 * ```php
 * <?php
 *
 * $obj = new StringTokenList;
 *
 * $obj[] = 'foo';          // Equivalent to $obj->add('foo');
 * $obj['baz'] = true;      // Equivalent to $obj->add('baz');
 * $obj['baz'] = false;     // Equivalent to $obj->remove('baz');
 * unset( $obj['foo'] );    // Equivalent to $obj->remove('foo');
 * unset( $obj[0] );        // Equivalent to $obj->remove( $obj->item(0) );
 * $obj['foo'] = 'qux';     // Equivalent to $obj->replace('foo', 'qux');
 * isset( $obj['foo'] );    // Equivalent to $obj->contains('foo');
 * $obj['foo'];             // Equivalent to $obj->contains('foo');
 * $obj[0];                 // Equivalent to $obj->item(0);
 * ```
 *
 * @method add( string|string[] $tokens )    Adds token(s) to the list of tokens.
 * @method remove( string|string[] $tokens ) Removes token(s) from the list of tokens.
 *
 * @property-read string[] $value Retrieves the list of tokens.
 */
class StringTokenList implements
	Iterator,
	ArrayAccess,
	Countable
{

// Properties
// ----------------------------------------------------------------------------

	/**
	 * The number of tokens in the list.
	 *
	 * The range of valid token indices is 0 to _length - 1_ inclusive.
	 *
	 * @var array
	 */
	protected $tokens;

	/**
	 * The current position in the list of tokens.
	 *
	 * @var int
	 */
	private $position;



// Methods
// ----------------------------------------------------------------------------

	/**
	 * Retrieve a new StringTokenList object
	 *
	 * @param string|string[] $tokens One or more case-sensitive tokens.
	 */
	public function __construct( $tokens = [] )
	{
		$this->clear()->rewind();

		if ( $tokens ) {
			$this->add( $tokens );
		}
	}

	/**
	 * Dynamically retrieve a value from the StringTokenList.
	 *
	 * @param string  $key
	 * @param mixed[] $arguments
	 *
	 * @return mixed
	 */
	public function __call( $name, $arguments )
	{
		switch ( $name ) {
			case 'add':
			case 'remove':
				return $this->__modify( $name, $arguments );
				break;
		}
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
			case 'value':
				return $this->tokens;
				break;
		}
	}

	/**
	 * Retrieves the concatenation of the tokens.
	 *
	 * @return string Stringified token list.
	 */
	public function __toString()
	{
		return implode( $this->separator(), $this->tokens );
	}

	/**
	 * Empty the list of tokens
	 *
	 * @return $this
	 */
	public function clear()
	{
		$this->tokens = [];

		return $this;
	}

	/**
	 * Retrieve the token at the specified index.
	 *
	 * @param int $offset The offset to retrieve.
	 *
	 * @return string|null Returns token if it exists, or NULL on failure.
	 */
	public function item( $offset )
	{
		$offset = (int) $offset;

		if ( isset( $this->tokens[ $offset ] ) ) {
			return $this->tokens[ $offset ];
		}

		return null;
	}

	/**
	 * Checks if a the list of tokens contains the provided token.
	 *
	 * @param string A case-sensitive string representing the token.
	 *
	 * @return bool Returns TRUE if the token exists otherwise FALSE.
	 */
	public function contains( $token )
	{
		$token = (string) $token;
		$this->__validate( $token );

		return $this->__contains( $token );
	}

	/**
	 * Replace an existing token for a new one.
	 *
	 * If the $old_token does not exist, the new one will not be added.
	 *
	 * This method is a shortcut for StringTokenList::remove( $old_token )
	 * followed by StringTokenList::add( $new_token ).
	 *
	 * @param string $old_token A case-sensitive string representing an existing token to remove.
	 * @param string $new_token A case-sensitive string representing a new token to append.
	 *
	 * @return $this
	 */
	public function replace( $old_token, $new_token )
	{
		$updated = false;

		$old_token = (string) $old_token;
		$new_token = (string) $new_token;

		$this->__validate( $old_token );
		$this->__validate( $new_token );

		if ( $index = array_search( $old_token, $this->tokens, true ) ) {
			$this->tokens[ $index ] = $new_token;
			$updated = true;
		}

		if ( $updated ) {
			$this->__update();
		}

		return $this;
	}

	/**
	 * If the name exists within the token list, it will be removed.
	 * If name does not exist, it will be added.
	 *
	 * Errors are not thrown If the token does not exist.
	 *
	 * @param string    $tokens A case-sensitive string representing a token.
	 * @param bool|null $force  When TRUE, adds the token (via self::add()).
	 *                          When FALSE, the token is removed (via self::remove()).
	 *                          If not used (undefined or simply non existent),
	 *                          normal toggle behavior ensues.
	 *                          Useful for adding or removing in one-step based on a condition.
	 *
	 * @return bool Returns TRUE if token is now present, and FALSE otherwise.
	 */
	public function toggle( $token, $force = null )
	{
		$output   = null;
		$contains = $this->contains( $token );

		if ( $contains ) {
			if ( ! $force ) {
				$this->__remove( [ $token ] );
				$change = false;
			}
			else {
				$change = true;
			}
		}
		else {
			if ( false === $force ) {
				$change = false;
			}
			else {
				$this->__add( [ $token ] );
				$change = true;
			}
		}

		return $change;
	}



// Callbacks
// ----------------------------------------------------------------------------

	/**
	 * Called if the token list has been updated.
	 *
	 * Rebases the array indices after token(s) are appended or removed.
	 */
	protected function __update()
	{
		$this->tokens = array_values( $this->tokens );
	}



// Internals
// ----------------------------------------------------------------------------

	/**
	 * Retrieves U+0020, a token delimiter.
	 *
	 * @link https://dom.spec.whatwg.org/#concept-ordered-set-serializer
	 *
	 * @return string
	 */
	protected function separator()
	{
		return chr( 0x20 );
	}

	/**
	 * Checks if a the list of tokens contains the provided token.
	 *
	 * @param string A case-sensitive string representing the token.
	 *
	 * @return bool Returns TRUE if the token exists otherwise FALSE.
	 */
	protected function __contains( $token )
	{
		return in_array( $token, $this->tokens, true );
	}

	/**
	 * Adds token(s) to the list of tokens.
	 *
	 * If token(s) already exists in the list of tokens,
	 * it will not add the token again.
	 *
	 * @param string[] $tokens One or more case-sensitive tokens.
	 *
	 * @return $this
	 */
	protected function __add( array $tokens )
	{
		$updated = false;

		foreach ( $tokens as $token ) {
			if ( ! $this->__contains( $token ) ) {
				$this->tokens[] = $token;
				$updated = true;
			}
		}

		if ( $updated ) {
			$this->__update();
		}

		return $this;
	}

	/**
	 * Removes token(s) from the list of tokens in a safe manner.
	 *
	 * Errors are not thrown If the token does not exist.
	 *
	 * @param string[] $tokens One or more case-sensitive tokens.
	 *
	 * @return $this
	 */
	protected function __remove( $tokens )
	{
		$updated = false;

		foreach ( $tokens as $token ) {
			if ( $this->__contains( $token ) ) {

			if ( false !== ( $index = array_search( $token, $this->tokens, true ) ) )
				unset( $this->tokens[ $index ] );
				$updated = true;
			}
		}

		if ( $updated ) {
			$this->__update();
		}

		return $this;
	}

	/**
	 * Alters the list of tokens.
	 *
	 * When adding, if token(s) already exists in the list of tokens,
	 * it will not add the token again.
	 *
	 * When removing, errors are not thrown If the token does not exist.
	 *
	 * @param string|string[] $tokens One or more case-sensitive tokens.
	 *
	 * @return $this
	 */
	protected function __modify( $action, $arguments )
	{
		$tokens = $arguments;
		$method = "__{$action}";

		if ( 1 === count( $arguments ) ) {
			$tokens = reset( $tokens );

			if ( is_string( $tokens ) ) {
				$tokens = explode( ' ', $tokens );
			}
		}

		if ( is_array( $tokens ) ) {
			$tokens = array_map( 'strval', $tokens );
			$tokens = array_filter( $tokens, 'strlen' );
		}

		array_walk( $tokens, [ $this, '__validate' ] );

		if ( method_exists( $this, $method ) ) {
			call_user_func( [ $this, $method ], $tokens );
		}

		return $this;
	}

	/**
	 * Retrieves the concatenation of the tokens.
	 *
	 * @throws InvalidArgumentException If the provided token is not a string,
	 *                                  is empty, or contains whitespace.
	 *
	 */
	protected function __validate( $token )
	{
		if ( strlen( $token ) === 0 ) {
			throw new InvalidArgumentException('The token is empty.');
		}

		if ( preg_match( '@\s@', $token ) ) {
			throw new InvalidArgumentException('Invalid token. Must not contain whitespace: ' . $token );
		}
	}



// Iterator Methods
// ----------------------------------------------------------------------------

	/**
	 * Return the current token
	 *
	 * @link http://php.net/manual/en/iterator.current.php
	 *
	 * @return mixed
	 */
	public function current()
	{
		return $this->item( $this->position );
	}

	/**
	 * Return the key of the current token
	 *
	 * @link http://php.net/manual/en/iterator.key.php
	 *
	 * @return int|null Returns integer on success, or NULL on failure.
	 */
	public function key()
	{
		return $this->position;
	}

	/**
	 * Move forward to next token
	 *
	 * Moves the current position to the next token.
	 *
	 * @link http://php.net/manual/en/iterator.next.php
	 */
	public function next()
	{
		++$this->position;
	}

	/**
	 * Rewind the Iterator to the first token
	 *
	 * Rewinds back to the first token of the Iterator.
	 *
	 * @link http://php.net/manual/en/iterator.rewind.php
	 */
	public function rewind()
	{
		$this->position = 0;
	}

	/**
	 * Checks if current position is valid
	 *
	 * This method is called after self::rewind() and self::next()
	 * to check if the current position is valid.
	 *
	 * @link http://php.net/manual/en/iterator.valid.php
	 *
	 * @return bool
	 */
	public function valid()
	{
		return isset( $this->tokens[ $this->position ] );
	}



// ArrayAccess Methods
// ----------------------------------------------------------------------------

	/**
	 * Whether there's a token at the specified index.
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 *
	 * @param int|string $offset An offset to check for.
	 *
	 * @return bool Returns TRUE if the token exists otherwise FALSE.
	 */
	public function offsetExists( $offset )
	{
		return $this->contains( $offset );
	}

	/**
	 * Retrieve the token at the specified index.
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 *
	 * @param int|string $offset The offset to retrieve.
	 *
	 * @return string|null Returns token if it exists, or NULL on failure.
	 */
	public function offsetGet( $offset )
	{
		if ( is_int( $offset ) ) {
			return $this->item( $offset );
		}
		else {
			return $this->contains( $offset );
		}
	}

	/**
	 * Append a token to the list and optionally remove an existing token.
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 *
	 * @param int|null        $offset The index is ignored.
	 * @param int|string|null $value  The value to set.
	 */
	public function offsetSet( $offset, $value )
	{
		$is_offset_string = is_string( $offset );
		$is_value_string  = is_string( $value );

		/** e.g., `$obj[] = 'foo';` */
		if ( null === $offset && $is_value_string ) {
			$this->add( $value );
		}
		elseif ( is_bool( $value ) && $is_offset_string ) {
			/** e.g., `$obj['foo'] = true;` */
			if ( $value ) {
				$this->add( $offset );
			}
			/** e.g., `$obj['foo'] = false;` */
			else {
				$this->remove( $offset );
			}
		}
		elseif ( $is_offset_string && $is_value_string ) {
			/** e.g., `$obj['foo'] = 'bar';` */
			$this->replace( $offset, $value );
		}
	}

	/**
	 * Remove a token.
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 *
	 * @param int|string $offset The token to unset.
	 */
	public function offsetUnset( $offset )
	{
		if ( is_int( $offset ) ) {
			unset( $this->tokens[ $offset ] );
		}
		else {
			$this->remove( $offset );
		}
	}



// Countable Methods
// ----------------------------------------------------------------------------

	/**
	 * Count the tokens in the list
	 *
	 * @link http://php.net/manual/en/countable.count.php
	 *
	 * @return int
	 */

	public function count()
	{
		return count( $this->tokens );
	}
}
