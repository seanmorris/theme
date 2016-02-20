<?php
namespace SeanMorris\Theme\Test;
class ThemeTest extends HtmlTestCase
{
	public function testRender()
	{
		$foo = new \SeanMorris\Theme\Test\Foo();

		ob_start();
		echo \SeanMorris\Theme\Test\TestTheme\Theme::render($foo);

		$output = ob_get_contents();
		ob_end_clean();

		$this->assertEqual(
			$output
			, '<p>FooView--0--1--2</p>'
			, 'Bad rendering of Foo under SeanMorris\Theme\Test\TestTheme\Theme'
		);
	}

	public function testContextRender()
	{
		$randomObject = new \SeanMorris\Theme\Test\RandomObject;

		$output = $randomObject->doThing();

		$this->assertEqual(
			$output
			, '<p>FooView--0--1--3</p>'
			, 'Bad rendering of Foo under SeanMorris\Theme\Test\OtherTestTheme within SeanMorris\Theme\Test\RandomObject'
		);
	}

	public function testFallbackTheme()
	{
		$foo = new \SeanMorris\Theme\Test\Foo();

		ob_start();
		echo \SeanMorris\Theme\Test\OtherTestTheme\Theme::render($foo);

		$output = ob_get_contents();
		ob_end_clean();

		$this->assertEqual(
			$output
			, '<p>FooView--0--1--2</p>'
			, 'Bad rendering of Foo under SeanMorris\Theme\Test\OtherTestTheme'
		);
	}

	public function testWrapper()
	{
		ob_start();
		echo \SeanMorris\Theme\Test\TestTheme\Theme::wrap('lol');

		$output = ob_get_contents();
		ob_end_clean();

		$this->assertEqual(
			$output
			, '<html><body>lol</body></html>'
			, 'Bad wrapping under SeanMorris\Theme\Test\TestTheme\Theme'
		);

		ob_start();
		echo \SeanMorris\Theme\Test\OtherTestTheme\Theme::wrap('lol');

		$output = ob_get_contents();
		ob_end_clean();

		$this->assertEqual(
			$output
			, '<html><body><b>lol</b></body></html>'
			, 'Bad wrapping under SeanMorris\Theme\Test\OtherTestTheme\Theme'
		);
	}
}