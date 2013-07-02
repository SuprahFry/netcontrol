<?php
require_once('includes/command.php');
require_once('includes/class_quake3.php');

class QQuery extends Command
{
	function run($parameters)
	{
		global $terminal;
		
		if (count($parameters) != 1)
		{
			$this->help();
			return;
		}
		
		$parsedURL = parse_url($parameters[0]);
		$quake = new Quake3();
		$result = false;
		
		if (array_key_exists('host', $parsedURL))
		{
			if (array_key_exists('port', $parsedURL))
			{
				$result = $quake->queryServerInfo($parsedURL['host'], $parsedURL['port']);
			}
			else
			{
				$result = $quake->queryServerInfo($parsedURL['host'], 28960);
			}
		}
		else
		{
			$result = $quake->queryServerInfo($parsedURL['path'], 28960);
		}
		
		if ($result !== false)
		{
			$terminal->writeLine($this->color($this->arr2String(': ', $result)));
			return;
		}
		
		$terminal->writeLine('The server could not be reached.');
	}
	
	function arr2String($glue, $array)
	{
		$toReturn = '';
		
		foreach ($array as $key => $value)
		{
			$toReturn .= $key.$glue.$value."\n";
		}
		
		return $toReturn;
	}
	
	function help()
	{
		global $terminal;
		$terminal->writeLine('Quake 3 Server Query Utility');
		$terminal->writeLine('Example: qquery 127.0.0.1:28960');
	}
	
	function color($string)
	{
		global $settings;
		return $this->nonClosingColor($string, $settings['colors']['cod4'], '^');
	}
}
?>