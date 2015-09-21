<?php
namespace SeanMorris\Theme\Test\OtherTestTheme;
class Theme extends \SeanMorris\Theme\Theme
{
	protected static
		$themes = ['SeanMorris\Theme\Test\TestTheme\Theme']
		, $contextView = [
			'SeanMorris\Theme\Test\RandomObject' => [
				'SeanMorris\Theme\Test\Foo' => 'SeanMorris\Theme\Test\OtherTestTheme\FooView'
			]
		]
		, $wrap = [
			'SeanMorris\Theme\Test\OtherTestTheme\Wrapper'
			, 'SeanMorris\Theme\Test\TestTheme\Wrapper'
		]
	;
}
