<?php
require_once('includes/command.php');
require_once('includes/class_rcon.php');

class RCON extends Command
{	
	function run($parameters)
	{
		global $terminal;
		$requestParameters = array('-h', '-p', '-r');
		$parameters = $this->parseFlaggedParameters($requestParameters, $parameters);
		$missing = $this->missingKeys($parameters, $requestParameters);
		
		if ($missing === false)
		{
			$this->help();
			return;
		}
		
		$parsedURL = parse_url($parameters['h']);
		$rcon = null;
		
		if (array_key_exists('host', $parsedURL))
		{
			if (array_key_exists('port', $parsedURL))
			{
				$rcon = new RCONSocket($parsedURL['host'], $parsedURL['port']);
			}
			else
			{
				$rcon = new RCONSocket($parsedURL['host']);
			}
		}
		else
		{
			$rcon = new RCONSocket($parsedURL['path']);
		}
		
		//$rcon = new RCONSocket($parsedURL['host'], $parsedURL['port']);
		
		if (!$rcon->connect())
		{
			$terminal->writeLine('Failed to connect. '.$rcon->getErrorString());
			return;
		}
		
		if ($rcon->login($parameters['p']))
		{
			$result = $rcon->commandGetResponse($parameters['r']);
			
			if ($result === false)
			{
				$terminal->writeLine('Failed to communicate. '.$rcon->getErrorString());
				return;
			}
			
			$terminal->writeLine($this->color($result['s1']));
		}
		else
		{
			$terminal->writeLine('Failed to communicate with server or wrong password.');
		}
		
		$rcon->disconnect();
	}
	
	function help()
	{
		global $terminal;
		$terminal->writeLine('Valve RCON Utility (Minecraft, Counter-Strike, Half-Life, etc.)');
		$terminal->writeLine('Example: rcon -h 127.0.0.1:25565 -p password -r help');
	}
	
	function color($string)
	{
		global $settings;
		return $this->nonClosingColor($string, $settings['rcon']['colors'], '§');
	}
	
	const RCON_AUTH = 3;
	const RCON_ECOMMAND = 2;
}
?>