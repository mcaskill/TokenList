TokenList
=========

The **`StringTokenList`** class represents a set of distinct space-separated tokens. It provides the main functionalities of a PHP Array. The main differences are that the `StringTokenList`:

- allows only integers as indexes,
- is always case-sensitive,
- and excludes duplicate values.

The `StringTokenList` class is essentially a PHP implementation of the [`DOMTokenList`](https://developer.mozilla.org/en-US/docs/Web/API/DOMTokenList) JavaScript interface.

The class is extended by:

- `DOMTokenList`
  - `DOMClassList`
  - `DOMRelList`

## Installation

### With Composer

```
$ composer require mcaskill/tokenlist
```

```json
{
	"require": {
		"mcaskill/tokenlist": "dev-master"
	}
}
```

```php
<?php

require 'vendor/autoload.php';

use StringTokenList;

printf( (string) ( new StringTokenList([ 'foo', 'baz', 'qux' ]) ) );
```
### Without Composer

Why are you not using [composer](http://getcomposer.org/)? Download the repository and save the files into your project path somewhere.

```php
<?php

require 'path/to/StringTokenList.php';
require 'path/to/DOMTokenList.php';

use DOMTokenList;

printf( ( new DOMTokenList([ 'foo', 'baz', 'qux' ]) )->attr() );
```

## Examples

Consult source code for each class for additional usage examples.

**Example #1 Basic Usage**

```php
<?php

$obj = new StringTokenList;

$obj->add('foo baz qux');
var_dump( $obj->value );

$obj->add([ 'foo', 'not', 'qux', 'xor' ]);
var_dump( $obj->value );

$obj->remove([ 'foo', 'qux' ]);
var_dump( $obj->value );

$obj->replace( 'not', 'and' );
var_dump( $obj->value );

$obj->toggle( 'foo' );
var_dump( $obj->value );

$obj->toggle( 'foo' );
var_dump( $obj->value );

var_dump( $obj->contains('and') );

var_dump( $obj->item(1) );

var_dump( (string) $obj );

var_dump( count( $obj ) ); // Equivalent to $obj->count();
```

The above example will output something similar to:

```
array (
  0 => 'foo',
  1 => 'baz',
  2 => 'qux',
)
array (
  0 => 'foo',
  1 => 'baz',
  2 => 'qux',
  3 => 'not',
  4 => 'xor',
)
array (
  0 => 'baz',
  1 => 'not',
  2 => 'xor',
)
array (
  0 => 'baz',
  1 => 'and',
  2 => 'xor',
)
array (
  0 => 'baz',
  1 => 'and',
  2 => 'xor',
  3 => 'foo',
)
array (
  0 => 'baz',
  1 => 'and',
  2 => 'xor',
)
bool(true)
string(3) "xor"
string(11) "baz xor and"
int(3)
```

**Example #2 Syntactic Sugar**

```php
<?php

$obj = new StringTokenList;

$obj[] = 'foo';          // Equivalent to $obj->add('foo');
$obj['baz'] = true;      // Equivalent to $obj->add('baz');
$obj['baz'] = false;     // Equivalent to $obj->remove('baz');
unset( $obj['foo'] );    // Equivalent to $obj->remove('foo');
unset( $obj[0] );        // Equivalent to $obj->remove( $obj->item(0) );
$obj['foo'] = 'qux';     // Equivalent to $obj->replace('foo', 'qux');
isset( $obj['foo'] );    // Equivalent to $obj->contains('foo');
$obj['foo'];             // Equivalent to $obj->contains('foo');
$obj[0];                 // Equivalent to $obj->item(0);
```
