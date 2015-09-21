<?php
namespace SeanMorris\Theme\Test;
class ViewTest extends \UnitTestCase
{
	public function testRender()
	{
		$foo = new \SeanMorris\Theme\Test\Foo();

		$fooView = new \SeanMorris\Theme\Test\TestTheme\FooView([
			'object' => $foo
		]);

		ob_start();
		
		echo $fooView;

		$output = ob_get_contents();
		ob_end_clean();

		$this->assertEqual(
			$output
			, '<p>FooView--0--1--2</p>'
			, 'Bad rendering of Foo by SeanMorris\Theme\Test\TestTheme\FooView'
		);
	}

	public function testSubclassRender()
	{
		$foo = new \SeanMorris\Theme\Test\Foo();

		$fooView = new \SeanMorris\Theme\Test\OtherTestTheme\FooView([
			'object' => $foo
		]);

		ob_start();

		echo $fooView;

		$output = ob_get_contents();
		ob_end_clean();

		$this->assertEqual(
			$output
			, '<p>FooView--0--1--3</p>'
			, 'Bad rendering of Foo by SeanMorris\Theme\Test\OtherTestTheme\FooView'
		);
	}
}