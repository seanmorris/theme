# SeanMorris\Theme

## Simple, sane templating and theming for PHP

[![Build Status](https://travis-ci.org/seanmorris/theme.svg?branch=master)](https://travis-ci.org/seanmorris/theme) [![Latest Stable Version](https://poser.pugx.org/seanmorris/theme/v/stable)](https://packagist.org/packages/seanmorris/theme) [![Total Downloads](https://poser.pugx.org/seanmorris/theme/downloads)](https://packagist.org/packages/seanmorris/theme) [![Latest Unstable Version](https://poser.pugx.org/seanmorris/theme/v/unstable)](https://packagist.org/packages/seanmorris/theme) [![License](https://poser.pugx.org/seanmorris/theme/license)](https://packagist.org/packages/seanmorris/theme)

The aim of the Theme library is to provide a start separation of presentation from logic. It has only two responsibilities:

* Mapping objects to Views
* Rendering those Views

## Composer

Just run `composer require seanmorris/theme` in your project directory.

You can also add `"seanmorris/theme": "^1.0.0"` to the `require` array in your project's composer.json.

```json
"require": {
  "seanmorris/theme": "^1.0.0"
}
```


## Templating

Coupling the template with the View class is very simple. Simply subclass the provided View and append the template after a call to `__halt_compiler();` (WITH THE CLOSING `?>`) like so:

(note: Short tags are enabled for simple echo statements as of PHP 5.4, but are not required)

```php
<?php
class FooView extends \SeanMorris\Theme\View
{
}
__halt_compiler(); ?>
<h1>FooView</h1>
<span class = "some_class"><?=$a;?></span>
<p><?=$b;?>. <b><?=$c;?></b></p>
```

Pass an associative array into the constructor to populate the variables in the template. The keys of the array will be translated to variable names. 

```php
<?php
$view = new FooView([
  'a' => 'value'
  , 'b' => 'value'
  , 'c' => 'value'
]);

echo $view;
```

## Preprocessing

Preprocessing templates is simple. Just implement the ```preprocess``` method in your view class, and you'll get a chance to operate on the variables prior to rendering.

```php
<?php
class FooView extends \SeanMorris\Theme\View
{
  public function preprocess(&$vars)
  {
    $vars['a'] = $vars['a'] . '...';
    $vars['b'] = $vars['b'] . '?';
    $vars['c'] = $vars['b'] . '!'
  }
}
__halt_compiler(); ?>
<h1>FooView</h1>
<span class = "some_class"><?=$a;?></span>
<p><?=$b;?>. <b><?=$c;?></b></p>
```

Usage:

```php
<?php
$view = new FooView([
  'a' => 'value'
  , 'b' => 'value'
  , 'c' => 'value'
]);

echo $view;
```

## Theming

Creating a theme is as simple as extending the theme class and providing a mapping from your object classes to their view classes, as shown:

```php
<?php
class Theme extends \SeanMorris\Theme\Theme
{
  protected static
  $view = [
    'SeanMorris\Foo' => 'SeanMorris\Theme\FooView'
  ];
}
```

Usage:

```php
<?php echo Theme::render(new Foo(...)); ?>
```

## Wrapping

If you've got a default "trim" you'd like to use to wrap everything (i.e. the view that contains your <html> <head>, and <body> structure), simply set the static property $wrap to an array listing your wrappers, innermost to outtermost.

```php
<?php
class Theme extends \SeanMorris\Theme\Theme
{
  protected static
  $wrap = [
    'SeanMorris\Theme\Wrapper'
    , 'SeanMorris\Theme\HtmlDocument' 
  ];
}
```
Usage:

```php
<?php
$bodyText = 'Lorem ipsum dolor sit amet...';
echo Theme::wrap($bodyText);
```

## Advanced Stuff...

Although the library doesn't do much, its got some power under the hood.

## Fallback Themes

If a theme cannot render an object, it can defer the rendering to other themes that can. This is done by specifying the $themes static property. The list will be check in order, until a theme is able to render a given object.

```php
<?php
class Theme extends \SeanMorris\Theme\Theme
{
  protected static
  $themes = [
    'SeanMorris\SomeTheme\Theme'
    , 'SeanMorris\SomeOtherTheme\Theme'
  ];
}
```

## Subclassing Views

You can subclass any view class and keep the template by ommitting the call to __halt_compiler(), and extending the existing view. You'll probably want to override the parent preprocessor as well.

```php
<?php
class FoozleView extends FooView
{
  public function preprocess(&$vars)
  {
    parent::preprocess($vars);
    $vars['a'] = $vars['object']->a . 'DIFFERENT!!!';
  }
}
```

## Contextualized Themeing

By defining mappings from classes to views in the $contextViews array, you can specify that a view should be rendered differently when the render call is made from certain classes.

In this example `SeanMorris\Stuff\Foo` will be rendered with the `SeanMorris\Theme\FooAlternateView` class when rendered inside of the `SeanMorris\Stuff\RandomObject` class, but outside, it will be rendered with `SeanMorris\Theme\FooView`.

```php
<?php
class Theme extends \SeanMorris\Theme\Theme
{
  protected static
  $contextView = [
    'SeanMorris\Stuff\RandomObject' => [
      'SeanMorris\Stuff\Foo' => 'SeanMorris\Theme\FooAlternateView'
    ]
  ]
  , $view = [
    'SeanMorris\Stuff\Foo' => 'SeanMorris\Theme\FooView'
  ];
}
```
