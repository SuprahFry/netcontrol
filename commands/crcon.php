<?php
require_once('includes/command.php');

class CRCON extends Command
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
		$ip = '127.0.0.1';
		$port = 28960;
		
		if (array_key_exists('host', $parsedURL))
		{
			if (array_key_exists('port', $parsedURL))
			{
				$port = $parsedURL['port'];
			}
			
			$ip = $parsedURL['host'];
		}
		else
		{
			$ip = $parsedURL['path'];
		}
		
		if ($result = $this->color($this->query($ip, $port, $parameters['p'], $parameters['r'])))
		{
			$terminal->writeLine($result);
			return;
		}
		
		$terminal->writeLine('Failed to connect to server.');
	}
	
	function help()
	{
		global $terminal;
		$terminal->writeLine('Call of Duty RCON Utility');
		$terminal->writeLine('Example: crcon -h 127.0.0.1:28960 -p password -r serverinfo');
	}
	
	function color($string)
	{
		global $settings;
		return $this->nonClosingColor($string, $settings['colors']['cod4'], '^');
	}
	
	function query($ip, $port, $rcon_pass, $command)
	{
		$socket = fsockopen('udp://'.$ip, $port, $errno, $errstr, 5);
		socket_set_timeout($socket, 5);

		if (!$socket)
		{
			return false;
		}
		
		$query = "\xFF\xFF\xFF\xFFrcon \"".$rcon_pass."\" ".$command;
		fwrite($socket, $query);
		
		$data = '';
		
		while ($d = fread ($socket, 10000))
		{
			$data .= $d;
		}
		
		fclose($socket);
		$data = preg_replace("/....print\n/", '', $data);
		return $data;
	}
}
?>