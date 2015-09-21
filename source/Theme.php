<?php
namespace SeanMorris\Theme;
class Theme
{
	protected static
		$contextView = []
		, $view = []
		, $themes = []
	;

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

			if(!isset($level['object']) && isset($level['class']))
			{
				$next = $level['class'];
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
				$objectStack[] = $next;
			}
		}

		$viewListList = [static::$view];

		foreach($objectStack as $superObject)
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

	public static function render($object, array $vars = [], $type = null)
	{
		if($view = static::resolveFirst($object, null, $type))
		{
			return new $view(['object' => $object] + $vars);
		}
	}

	public static function stack($elements, $stopClass = null)
	{
		if($view = static::resolveFirst('stack', $stopClass))
		{
			return new $view($elements);
		}
	}

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