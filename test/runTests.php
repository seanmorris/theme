<?php
chdir(__DIR__);

require '../vendor/autoload.php';

$testClasses = [
	'SeanMorris\Theme\Test\ThemeTest'
	, 'SeanMorris\Theme\Test\ViewTest'
];

foreach($testClasses as $testClass)
{
	$test = new $testClass;
	$test->run(new \TextReporter());	
}
