<?php
namespace SeanMorris\Theme;
class View
{
	protected
	$haltLine  = 0
	, $vars    = [];

	public function __construct($vars = [])
	{
		$this->update($vars);
	}

	public function update($vars)
	{
		$this->vars = $vars + $this->vars;

		$this->preprocess($this->vars);
	}

	protected function preprocess(&$vars)
	{
		
	}

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

		$renderScope = function() use($template, $vars, $classFile)
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
		};

		return $renderScope();
	}

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