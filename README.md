# SeanMorris\Theme

## Simple, sane templating and theming for PHP

The aim of the Theme library is to provide a start separation of presentation from logic. It has only two responsibilities:

* Mapping objects to Views
* Rendering those Views

## Templating

Coupling the template with the View class is very simple. Simply subclass the provided View and append the template after a call to __halt_compiler() like so:

```php
<?php
class FooView extends \SeanMorris\Theme\View
{
}
__halt_compiler();
?>
<p>FooView--<?=$a;?>--<?=$b;?>--<?=$c;?></p>
```

Usage of this view would work as such:

```php
<?php
$view = new FooView([
  'a' => 1
  , 'b' => 2
  , 'c' => 3
]);

echo $view;
?>
```

## Preprocessing

Preprocessing templates is simple. Just implement the ```preprocess``` method in your view class, and you'll get a chance to operate on the variables prior to rendering.

```php
<?php
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
```

Usage:

```php
<?php
$view = new FooView([
  'object' => new Foo
]);

echo $view;
?>
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
    ]
  ;
}
?>
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
  ;
}
?>
```
Usage:

```php
<?php
$bodyText = 'Lorem ipsum dolor sit amet...';
echo Theme::wrap($bodyText);
?>
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
    ]
  ;
}
?>
```

## Subclassing Views

You can subclass any view class and keep the template by ommitting the call to __halt_compiler(), and extending the existing view. You'll probably want to override the parent preprocessor as well.

```php
<?php
class FoozleView extends FooView
{
  public function preprocess(&$vars)
  {
    parent::preprocess();
    $vars['a'] = $vars['object']->a . 'DIFFERENT!!!';
  }
}
?>
```

## Contextualized Themeing

By defining mappings from classes to views in the $contextViews array, you can specify that a view should be rendered differently when the render call is made from certain classes.

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
    ]
  ;
}
?>
```

Now SeanMorris\Stuff\Foo will be rendered with the SeanMorris\Theme\FooAlternateView class when rendered inside of the SeanMorris\Stuff\RandomObject class, but outside, it will be rendered with SeanMorris\Theme\FooView.
