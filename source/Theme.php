<?php
namespace SeanMorris\Theme;
/**
 * The Theme class maps object classes to views classes.
 *
 * @package SeanMorris\Theme
 */
abstract class Theme
{
	protected static
		/**
		 * List of list of views classes, first keyed by object context, then object class.
		 */
		$contextView = []
		/**
		 * List of views classes, keyed by object class.
		 */
		, $view = []
		/**
		 * Themes to try if the current one cannot render a given object.
		 */
		, $themes = []
	;

	/**
	 * Resolves a list of classes or other string values
	 * for a given renderKey.
	 * 
	 * @param string $renderKey string to resolve values for.
	 * @param string $stopClass stop searching the calling context if this class is found.
	 * @param string $type type of list to resolve.
	 * 
	 * @return list of string classes or string values mapped from renderKey
	 */
	public static function resolveList($renderKey, $stopClass = null, $type = null)
	{
		$viewListList = static::resolve($renderKey, $stopClass, $type);
		$finalList = [];

		foreach($viewListList as $theme => $viewList)
		{
			foreach($viewList as $view)
			{
				if(is_array($view))
				{
					$finalList = array_merge($finalList, $view);
				}
				else
				{
					$finalList[] = $view;
				}
			}
		}

		return array_unique($finalList);
	}

	/**
	 * Resolves a single class or other string value
	 * for a given renderKey.
	 * 
	 * @param string $renderKey string to resolve values for.
	 * @param string $stopClass stop searching the calling context if this class is found.
	 * @param string $type type of list to resolve.
	 * 
	 * @return string class or string value mapped from renderKey
	 */
	public static function resolveFirst($renderKey, $stopClass = null, $type = null)
	{
		$viewListList = static::resolve($renderKey, $stopClass, $type);
		$viewList = current($viewListList);
		
		if(!$viewList)
		{
			return;
		}

		$view = current($viewList);

		return $view;
	}

	/**
	 * Resolves a list of lists of classes or other string values.
	 * for a given renderKey.
	 * 
	 * Keyed by the relevant class.
	 * 
	 * @param string $renderKey string to resolve values for.
	 * @param string $stopClass stop searching the calling context if this class is found.
	 * @param string $type type of list to resolve.
	 *
	 * @return list of lists of string classes or string values mapped from renderKey
	 */
	public static function resolve($renderKey, $stopClass = null, $type = null)
	{
		$renderKey = $renderKey;
		$objectMode = false;

		if(is_object($renderKey))
		{
			$renderKey = get_class($renderKey);
			$objectMode = true;
		}

		if(class_exists($renderKey))
		{
			$objectMode = true;
		}

		$trace = debug_backtrace();
		$last = NULL;

		foreach($trace as $level)
		{
			$next = NULL;

			if(isset($level['object']))
			{
				$next = get_class($level['object']);
			}
			elseif(isset($level['class']))
			{
				$next = $level['class'];
			}			

			if($next === $last)
			{
				continue;
			}

			$last = $next;

			if($next)
			{
				$classStack[] = $next;
			}
		}

		$viewListList = [static::$view];

		foreach($classStack as $superObject)
		{
			if($superObject instanceof \SeanMorris\Ids\Router)
			{
				$routeClass	= get_class($superObject->routes());

				foreach(static::$contextView as $contextClass => $viewList)
				{
					if(is_a($routeClass, $contextClass, true))
					{
						array_push($viewListList, $viewList);
					}
				}
			}

			foreach(static::$contextView as $contextClass => $viewList)
			{
				if(is_a($superObject, $contextClass, true))
				{
					array_push($viewListList, $viewList);
				}
			}

			if($stopClass && $superObject instanceof $stopClass)
			{
				break;
			}
		}

		$relevantViewList = [];

		foreach($viewListList as $viewList)
		{
			foreach($viewList as $class => $renderer)
			{
				if($renderKey === $class)
				{
					$renderer = static::selectType($renderer, $type);
					
					if($renderer)
					{
						$relevantViewList[] = $renderer;
					}
				}
			}

			foreach($viewList as $class => $renderer)
			{
				if($objectMode && is_a($renderKey, $class, true))
				{
					$renderer = static::selectType($renderer, $type);
					
					if($renderer)
					{
						$relevantViewList[] = $renderer;
					}
				}
			}
		}

		$filterDuplicates = (bool)array_filter(
			$relevantViewList
			, function($view)
			{
				return !is_array($view);
			}
		);

		if($filterDuplicates)
		{
			$relevantViewList = array_unique($relevantViewList);
		}

		$relevantViewListList = [];

		if($relevantViewList)
		{
			$relevantViewListList[get_called_class()] = $relevantViewList;
		}

		$parentClass = get_parent_class(get_called_class());

		if($parentClass && $parentClass !== get_called_class())
		{
			$parentViewListList = $parentClass::resolve($renderKey, $stopClass, $type);

			if($parentViewListList)
			{
				$relevantViewListList = array_merge($relevantViewListList, $parentViewListList);
			}
		}

		foreach(static::$themes as $subTheme)
		{
			$subThemeViewListList = $subTheme::resolve($renderKey, $stopClass, $type);

			$subThemeViewList = current($subThemeViewListList);

			if($subThemeViewList)
			{
				$relevantViewListList = array_merge($relevantViewListList, $subThemeViewListList);
			}
		}

		return $relevantViewListList;
	}

	/**
	 * Render a given object with a view provided by the theme.
	 * 
	 * @param object $renderKey string to resolve values for.
	 * @param array $vars additional variables passed on to view
	 * @param type string type of view to return
	 * 
	 * @return object View object ready to be rendered..
	 */
	public static function render($object, array $vars = [], $type = null)
	{
		if($view = static::resolveFirst($object, null, $type))
		{
			return new $view(['object' => $object] + $vars);
		}
	}

	/**
	 * Stack a set of elements with the stack view provided by the theme.
	 *  
	 * @param array $elements Elements to stack.
	 * @param string $stopClass stop searching the calling context if this class is found.
	 * 
	 * @return object Stack view with elements.
	 */
	public static function stack($elements, $stopClass = null)
	{
		if($view = static::resolveFirst('stack', $stopClass))
		{
			return new $view($elements);
		}
	}

	/**
	 * Wrap text with the default trim.
	 *  
	 * @param string $body Value to be wrapped.
	 * 
	 * @return object Wrap view with body.
	 */
	public static function wrap($body)
	{
		if(isset(static::$wrap))
		{
			if($body instanceof \SeanMorris\Theme\View)
			{
				$body = $body->render();
			}

			foreach(static::$wrap as $wrapper)
			{
				$body = new $wrapper([
					'body' => $body
				]);	
			}
		}

		return $body;
	}

	/**
	 * Selects view type when provided and available
	 *
	 * @param array|object|bool $views List of views by type if available, single view class, or FALSE if no view found.
	 * @param string $type Type of view to select.
	 *  
	 * 
	 * @return object View object ready to be rendered..
	 */
	protected static function selectType($views, $type)
	{
		if(!is_array($views))
		{
			return $views;
		}

		if($type === FALSE)
		{
			return $views;
		}

		if(!$type)
		{
			return current($views);
		}

		if(!isset($views[$type]))
		{
			return null;
		}

		return $views[$type];
	}
}