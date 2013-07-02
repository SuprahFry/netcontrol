<?php
set_time_limit(0);
require_once('includes/class_rcon.php');
require_once('includes/class_quake3.php');
session_start();

if (!isset($_SESSION['q3']))
{
	$_SESSION['q3'] = new Quake3();
}

//$arguments = explode(' ', $_GET['command']);
$arguments = preg_split('/"([^"]*)"|\s/', $_GET['command'],
				NULL, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
$command = array_shift($arguments);
$result = '';

function arr2String($glue, $array)
{
	$toReturn = '';
	
	foreach ($array as $key => $value)
	{
		$toReturn .= $key.$glue.$value."\n";
	}
	
	return $toReturn;
}

switch($command)
{
	case 'qquery':
		$result = parseParameters(array('-h', '-p'), $arguments);
		$result = $_SESSION['q3']->queryServerInfo($result['h'], $result['p']);
		$result = colorCallOfDuty(arr2String(': ', $result));
		break;
	case 'crcon':
		$result = parseParameters(array('-h', '-p', '-P', '-r'), $arguments);
		//print_r($result);
		$result = crcon($result['h'], $result['p'], $result['P'], $result['r']);
		
		if ($result === false)
		{
			$result = 'Failed to connect to server.';
		}
		break;
	case 'rcon':
		if ($arguments[0] == 'help')
		{
			$result = 'Flags:<br />-h - host<br />-p - port';
			break;
		}
		
		$result = parseParameters(array('-h', '-p', '-P', '-r'), $arguments);
		
		if (array_key_exists('h', $result) && array_key_exists('p', $result))
		{
			$_SESSION['rcon'] = new RCONSocket($result['h'], $result['p']);
		}
		else if (array_key_exists('h', $result))
		{
			$_SESSION['rcon'] = new RCONSocket($result['h']);
		}
		
		if (!$_SESSION['rcon']->connect())
		{
			$result = 'Failed to connect to server. '.$_SESSION['rcon']->getErrorString();
			break;
		}
		
		if (isset($_SESSION['rcon']) && array_key_exists('P', $result) && array_key_exists('r', $result))
		{
			if ($_SESSION['rcon']->login($result['P']))
			{
				$result = $_SESSION['rcon']->commandGetResponse($result['r']);
				$result = colorMinecraft($result['s1']);
				$_SESSION['rcon']->disconnect();
			}
			else
			{
				$result = 'Login failed, wrong password.';
			}
		}
		else
		{
			$result = 'No command to run.';
		}
		break;
	case 'mping':
		$pingResult = ping($arguments[0]);
		
		if ($pingResult !== false)
		{
			$result = $pingResult['motd'].'<br />'.$pingResult['players'].'/'.$pingResult['max_players'].' players';
			break;
		}
		
		$result = 'Server could not be reached.';
		break;
	case 'help':
	case '?':
		$result = 'Available tools: <a href="#rcon">rcon</a>, <a href="#crcon">crcon</a>, <a href="#mping">mping</a>, <a href="#qquery">qquery</a>';
		break;
	default:
		$result = 'Unknown \''.htmlspecialchars($command).'\', try ? for help.';
		break;
}

//stuff();
//$res = implode(', ', ping($_GET['command']));
echo(json_encode(array('response' => nl2br($result))));

function parseParameters($noopt = array(), $params)
{
	$result = array();
	reset($params);
	
	while (list($tmp, $p) = each($params))
	{
		if ($p{0} == '-')
		{
			$pname = substr($p, 1);
			$value = true;
			
			if ($pname{0} == '-')
			{
				$pname = substr($pname, 1);
				
				if (strpos($p, '=') !== false)
				{
					list($pname, $value) = explode('=', substr($p, 2), 2);
				}
			}
			
			$nextparm = current($params);
			
			if (!in_array($pname, $noopt) && $value === true && $nextparm !== false && $nextparm{0} != '-')
			{
				list($tmp, $value) = each($params);
			}
			
			$result[$pname] = $value;
		}
		else
		{
			$result[] = $p;
		}
	}
	
	return $result;
}

function crcon($ip, $port, $rcon_pass, $command)
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

function stuff()
{
	$ip = '127.0.0.1';
	$port = 4040;
	
	$socket = fsockopen($ip, $port);
	
	if ($socket === false) {
		die('failed to open socket');
	}
	
	for ($i = 0; $i < 256; $i++) {
		fwrite($socket, chr($i));
	}
	
	fclose($socket);
}

function nonClosingColor($data, $colors, $carat)
{
	$lines = explode("\n", $data);
	$newData = array();
	
	foreach ($lines as $line)
	{
		$open = false;
		$section = false;
		$len = strlen($line);
		$newLine = '';
		
		for ($i = 0; $i < $len; $i++)
		{
			if ($line{$i} == $carat)
			{
				$section = true;
				
				if ($open)
				{
					$newLine .= '</span>';
					$open = false;
				}
			}
			else if ($section)
			{
				$open = true;
				$section = false;
				$newLine .= '<span style="color: #'.$colors[$line{$i}].';">';
			}
			else
			{
				$newLine .= $line{$i};
			}
		}
		
		if ($open)
		{
			$newLine .= '</span>';
			$open = false;
		}
		
		$newData[] = $newLine;
	}
	
	return implode("\n", $newData);
}

function colorMinecraft($data)
{
	$colors = array('0' => '000000', '1' => '0000aa', '2' => '00aa00', '3' => '00aaaa', '4' => 'aa0000',
					'5' => 'aa00aa', '6' => 'ffaa00', '7' => 'aaaaaa', '8' => '555550', '9' => '5555ff',
					'a' => '55ff55', 'b' => '55ffff', 'c' => 'ff5555', 'd' => 'ff55ff', 'e' => 'ffff55',
					'f' => 'ffffff');
	return nonClosingColor($data, $colors, '§');
}

function colorCallOfDuty($data)
{
	$colors = array('1' => 'f15757', '2' => '00fb00', '3' => 'e8e803', '4' => '0000fe', '5' => '02e5e5',
					'6' => 'ff5cff', '7' => 'aaaaaa', '8' => '000000', '9' => '000000', '0' => '000000');
	return nonClosingColor($data, $colors, '^');
}

function ping($host, $port = 25565, $timeout = 5)
{
	$fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
	
	if (!$fp)
		return false;

	fwrite($fp, "\xFE");
	$d = fread($fp, 256);
	
	if ($d[0] != "\xFF")
		return false;

	$d = substr($d, 3);
	$d = mb_convert_encoding($d, 'auto', 'UCS-2');
	$d = explode("\xA7", $d);
	fclose($fp);
	return array('motd'			=> $d[0],
				 'players'		=> intval($d[1]),
				 'max_players'	=> intval($d[2]));
}
?>