<?php
require_once('includes/command.php');

class MPing extends Command
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
		$result = false;
		
		if (array_key_exists('host', $parsedURL))
		{
			if (array_key_exists('port', $parsedURL))
			{
				$result = $this->ping($parsedURL['host'], $parsedURL['port']);
			}
			else
			{
				$result = $this->ping($parsedURL['host']);
			}
		}
		else
		{
			$result = $this->ping($parsedURL['path']);
		}
		
		if ($result !== false)
		{
			$terminal->writeLine($result['motd']);
			$terminal->writeLine($result['players'].'/'.$result['max_players'].' players');
			return;
		}
		
		$terminal->writeLine('The server could not be reached.');
	}
	
	function help()
	{
		global $terminal;
		$terminal->writeLine('Minecraft Ping Utility');
		$terminal->writeLine('Example: mping 127.0.0.1:25565');
	}
	
	function ping($host, $port = 25565, $timeout = 5)
	{
		$server = @fsockopen($host, $port, $errno, $errstr, $timeout);
		
		if (!$server)
		{
			return false;
		}

		fwrite($server, "\xFE");
		$result = fread($server, 256);
		
		if ($result[0] != "\xFF")
		{
			return false;
		}
		
		$result = substr($result, 3);
		$result = mb_convert_encoding($result, 'auto', 'UCS-2');
		$result = explode("\xA7", $result);
		fclose($server);
		return array('motd'			=> $result[0],
					 'players'		=> intval($result[1]),
					 'max_players'	=> intval($result[2]));
	}
}
?>