<?php
/**
 * @package SeanMorris\Theme\Test
 */
namespace SeanMorris\Theme\Test;
class HtmlTestCase extends \UnitTestCase
{
	protected function xpathQuery($html, $xpath)
	{
		$dom = new \DomDocument;
		$dom->loadHtml($html);
		$domXpath = new \DomXPath($dom, LIBXML_NOERROR);
		return $domXpath->query($xpath);
	}

	protected function getTags($html, $tagname, array $attrs = [])
	{
		$attrString = NULL;
		foreach($attrs as $name => $value)
		{
			$operator = '=';
			if(is_array($value))
			{
				list($value, $operator) = $value;
			}
			$attrString .= sprintf('[@%s%s"%s"]', $name, $operator, $value);
		}

		$nodeList = $this->xpathQuery($html, $x = sprintf('//%s%s', $tagname, $attrString));
		$nodes = [];

		foreach($nodeList as $node)
		{
			$nodes[] = $node->ownerDocument->saveHtml($node);
		}

		return $nodes;
	}

	protected function getTag($html, $tagname, array $attrs = [])
	{
		$tags = $this->getTags($html, $tagname, $attrs);

		return current($tags);
	}

	protected function getAttr($tag, $attr)
	{
		$nodes = $this->xpathQuery($tag, '/html/body/*[1]');
		$attrVal = NULL;

		if ($nodes->length)
		{
			$attrVal = $nodes->item(0)->getAttribute($attr);
		}

		return $attrVal;
	}

	protected function getText($tag, $flatten = TRUE)
	{
		$nodes = $this->xpathQuery($tag, '/html/body/*[1]');
		$text = NULL;

		if ($nodes->length)
		{
			$text = $nodes->item(0)->textContent;
		}

		if($flatten)
		{
			$text = preg_replace(['/\n+/', '/\s+/'], ' ', $text);
		}

		return $text;	
	}

	protected function hasText($tag, $needle)
	{
		return strpos($this->getText($tag), $needle) !== FALSE;
	}
}
