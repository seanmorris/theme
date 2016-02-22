<?php
/**
 * @package SeanMorris\Theme\Test
 */
namespace SeanMorris\Theme\Test\TestTheme;
class Theme extends \SeanMorris\Theme\Theme
{
	protected static
		$view = [
			'SeanMorris\Theme\Test\Foo' => 'SeanMorris\Theme\Test\TestTheme\FooView'
			, 'SeanMorris\Theme\Test\Bar' => [
				'list' => 'SeanMorris\Theme\Test\TestTheme\BarView'
				, 'single' => 'SeanMorris\Theme\Test\TestTheme\BarListView'
			]
		]
		, $wrap = [
			'SeanMorris\Theme\Test\TestTheme\Wrapper'
		]
	;
}