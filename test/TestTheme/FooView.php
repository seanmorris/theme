<?php
namespace SeanMorris\Theme\Test\TestTheme;
class FooView extends \SeanMorris\Theme\View
{
	public function preprocess(&$vars)
	{
		$vars['a'] = $vars['object']->a;
		$vars['b'] = $vars['object']->b;
		$vars['c'] = $vars['object']->c;
	}
}
__halt_compiler();
?>
<p>FooView--<?=$a;?>--<?=$b;?>--<?=$c;?></p>