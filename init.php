<?php
ob_start();
require_once('includes/terminal.php');
require_once('config.php');
session_start();

//$_GET['command'] = 'rcon -h 127.0.0.1:25565 -p cocks -r ?';

$terminal = new Terminal(@$_GET['command']);

if (isset($_GET['command']) && $terminal->getCommand() != '' && $settings['terminal']['enabled'])
{
	$command = array_key_exists_nc($terminal->getCommand(), $settings['terminal']['commands']);

	if ($command !== false)
	{
		require_once($settings['terminal']['commands'][$command]);
		$arguments = $terminal->getArguments();
		$cl = new $command();
		
		if (@$arguments[0] == 'help')
		{
			$cl->help();
		}
		else
		{
			$cl->run($arguments);
		}
		
		$terminal->setValue('command', $command);
	}
	else
	{
		$terminal->writeLine('Command not found.');
		$terminal->writeLine('Available commands:');
		
		foreach ($settings['terminal']['commands'] as $key => $value)
		{
			$terminal->writeLine($key);
		}
	}
}

if (!$settings['terminal']['enabled'])
{
	$terminal->writeLine('Terminal has been disabled by an administrator.');
}

$terminal->setValue('ob', ob_get_clean());
$terminal->nl2br('response');
echo($terminal->getJSONOutput());
ob_end_flush();

function array_key_exists_nc($key, $search)
{ 
    if (array_key_exists($key, $search))
	{ 
        return $key; 
    } 
	
    if (!(is_string($key) && is_array($search) && count($search)))
	{ 
        return false; 
    } 
	
    $key = strtolower($key); 
	
    foreach ($search as $k => $v)
	{ 
        if (strtolower($k) == $key)
		{ 
            return $k; 
        } 
    } 
    return false; 
}
?>