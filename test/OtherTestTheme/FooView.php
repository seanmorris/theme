<?php
/**
 * @package SeanMorris\Theme\Test
 */
namespace SeanMorris\Theme\Test\OtherTestTheme; 
class FooView extends \SeanMorris\Theme\Test\TestTheme\FooView
{
	public function preprocess(&$vars)
	{
		parent::preprocess($vars);
		
		$vars['c'] = 3;
	}
}
