<?php
namespace SeanMorris\Theme;
/**
 * The View class maps preprocessing logic to templates.
 * 
 * @package SeanMorris\Theme
 */
abstract class View
{
	/**
	 * Line number where __halt_compiler() appears.
	 * @var array
	 */
	protected $haltLine  = 0;
	/**
	 * Variables for template.
	 * @var array
	 */
	protected $vars      = [];

	/**
	 * Sets up view object, consumes variables for template.
	 * 
	 * @param array $vars Variables for template.
	 */
	public function __construct($vars = [])
	{
		$this->update($vars);
	}

	/**
	 * Updates variables for template.
	 * 
	 * @param array $vars Variables for template.
	 */
	public function update($vars)
	{
		$this->vars = $vars + $this->vars;

		$this->preprocess($this->vars);
	}

	/**
	 * Preprocess variables for template, before render.
	 * 
	 * @param array $vars Variables for template.
	 */
	protected function preprocess(&$vars)
	{
		
	}

	/**
	 * Render self to string for display.
	 * 
	 * @param array $vars Variables for template.
	 * @param int $asc Max number of classes to ascend when looking for template.
	 * 
	 * @return string Rendered view.
	 */
	public function render($vars = [], $asc = 0)
	{
		$vars = $this->vars + $vars;
		$className = get_called_class();

		while($asc > 0)
		{
			$parentClass = get_parent_class($className);

			if(!$parentClass)
			{
				break;
			}

			$className = $parentClass;

			$asc--;
		}

		do {
			$reflection = new \ReflectionClass($className);
			$classFile = $reflection->getFileName();

			$fileContent = file_get_contents($classFile);
			$tokens = token_get_all($fileContent);
			$hasHalt = FALSE;

			foreach($tokens as $token)
			{
				if(is_array($token)
					&& isset($token[0], $token[2])
				) {
					if($token[0] == T_HALT_COMPILER)
					{
						$hasHalt = TRUE;
						$this->haltLine = $token[2] - 1;
					}
				}
			}

			if($hasHalt)
			{
				$template = $tokens[ count($tokens)-1 ][1];
				break;
			}

			$className = get_parent_class($className);
    
			if(!$className || $className == get_class())
			{
				throw new \Exception(sprintf(
					'Cannot locate template. '
					. 'No call to __halt_compiler() found along inheritance chain of %s.'
					, get_called_class()
				));
			}

		} while(!$hasHalt);

		$renderScope = \Closure::Bind(
			function() use($template, $vars, $classFile)
			{
				extract($vars);
				ob_start();

				try{
					eval($template);
				}
				catch(\Exception $e)
				{
					error_log(
						'Exception thrown in template: '
							. $classFile
							. ':'
							. ($e->getLine() + $this->haltLine)
							. PHP_EOL
							. $e->getMessage()
					);

					throw $e;
				}

				$content = ob_get_contents();
				ob_end_clean();

				return $content;
			}
			, $this
			, get_called_class()
		);

		$result = $renderScope();

		if(isset($vars['__debug']) && $vars['__debug'])
		{
			$result = sprintf(
				"<!-- START %s -->\n%s\n<!-- END %s --->"
				, get_called_class()
				, $result
				, get_called_class()
			);
		}

		return $result;
	}

	/**
	 * Renders the view.
	 * Absorbs and logs any errors during the render process.
	 * 
	 * @return string Rendered view.
	 */
	public function __toString()
	{
		try{
			$result = $this->render();
		} catch (\Exception $e) {
			error_log($e->getTraceAsString());
			$result = '!!!';
		}

		return (string)$result;
	}
}
