# mufuphlex array-util


[![Build Status](https://travis-ci.org/Mufuphlex/array-util.svg)](https://travis-ci.org/Mufuphlex/array-util)
[![Latest Stable Version](https://poser.pugx.org/mufuphlex/array-util/v/stable)](https://packagist.org/packages/mufuphlex/array-util)
[![License](https://poser.pugx.org/mufuphlex/array-util/license)](https://packagist.org/packages/mufuphlex/array-util)


Array utils - smart&fast array helpers.

These helpers, among other things, provide a <strong>significantly improved</strong> (about <strong>10-30 times faster</strong>) analogues of built-in PHP array functions, like <code>unique()</code> or <code>intersect()</code>.


### `unique()` function
This is an improved (**30 times faster!**) implementation of standard `array_unique()` PHP function.
```php
$arr = [1,2,3,1,2,3];
$unique = \Mufuphlex\Util\ArrayUtil::unique($arr);
// [1,2,3]
```
### `intersect()` function
This is an improved (**10 times faster!**) implementation of standard `array_intersect()` PHP function.
```php
$a = [1,2,3];
$b = [2,3,4];
$c = [3,4,5];
$result = \Mufuphlex\Util\ArrayUtil::intersect($a, $b, $c);
// [3]
```
### <code>cutBy*()</code> functions
If you need to remove particular elements from an array by elements' keys, `cutByWhitelist(array $array, array $map)` and `cutByBlacklist(array $array, array $map)` can be very useful. `cutByWhitelist()` **leaves** in `$array` only elements which are listed in the `$map` and `cutByBlacklist()` - just in opposite - **removes** from `$array` elements listed in `$map`. Moreover, these functions can not only remove, but also modify `$array`'s members via callbacks - smth like an extended version of standard PHP function `array_walk()`.

**How to use it?**

Let's consider the following sample array:

<pre>
$array = [
	'result' => [
		'one' => [
			'param 1' => 1,
			'param 2' => 2,
			'param 3' => 3,
		],
		'another' => [
			'param 1' => 4,
			'param 2' => 5,
			'param 3' => 6,
		],
		'another one' => [
			'param 1' => 7,
			'param 2' => 8,
			'param 3' => 9,
		]
	],
	'errors' => [
		'form' => [
			'field 1' => [
				0 => 'error 1',
				1 => 'error 2'
			],
			'field 2' => [
				0 => 'error 3',
				1 => 'error 4 '
			]
		],
		'logic' => [
			0 => 'logic error 1',
			1 => 'logic error 2'
		]
	],
	0 => [
		0 => 'item 1',
		1 => 'item 2'
	],
	1 => [
		0 => 'item 3',
		1 => 'item 4'
	],
	'123 - numeric containing' => [
		0 => 'item 5',
		1 => 'item 6'
	],
	'not numeric containing' => [
		0 => 'item 5',
		1 => 'item 6'
	],

];
</pre>
<ol>
<li>Leave/remove elements in/from `array.result*` with keys containing only `one` or only `another`:
<pre>
$map = [
	'result' => [
		'/^(?:one|another)$/' => true
	]
];
</pre>
</li>
<li>Leave/remove only elements in/from `array.errors.logic`:
<pre>
$map = [
	'errors' => [
		'logic' => true
	]
];
</pre>
</li>
<li>Leave/remove only elements with key `0` from `array.errors.form.field *.*`:
<pre>
$map = [
	'errors' => [
		'form' => [
			'field \d+' => [
				0 => true
			]
		]
	]
];
</pre>
</li>
<li>Leave/remove elements in/from `array.result.*` with keys `param 3` and/or modify these elements' values by adding 5:
<pre>
$map = [
	'result' => [
		'/.+/' => [
			'param 3' => function($arg){ return $arg+=5; }
		]
	]
];
</pre>
</li>
</ol>
