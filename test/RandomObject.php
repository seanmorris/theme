<?php
/**
 * @package SeanMorris\Theme\Test
 */
namespace SeanMorris\Theme\Test;
class RandomObject
{
	public function doThing()
	{
		$foo = new \SeanMorris\Theme\Test\Foo();

		ob_start();
		echo \SeanMorris\Theme\Test\OtherTestTheme\Theme::render($foo);

		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}
}