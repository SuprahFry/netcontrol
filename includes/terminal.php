<?php
class Terminal
{
	protected $output = array('response' => '');
	protected $arguments = array();
	protected $command = '';
	
	function Terminal($command)
	{
		$this->arguments = preg_split('/"([^"]*)"|\s/', $command,
			NULL, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		$this->command = array_shift($this->arguments);
	}
	
	function arrayToString($glue, $array)
	{
		$toReturn = '';
		
		foreach ($array as $key => $value)
		{
			$toReturn .= $key.$glue.$value."\n";
		}
		
		return $toReturn;
	}
	
	function createLink($to, $title)
	{
		$this->write('<a href="'.$to.'">'.$title.'</a>');
	}
	
	function write($data)
	{
		$this->output['response'] .= $data;
	}
	
	function writeLine($line)
	{
		$this->write($line."\n");
	}
	
	function setValue($key, $value)
	{
		$this->output[$key] = $value;
	}
	
	function nl2br($key)
	{
		$this->output[$key] = nl2br(rtrim($this->output[$key]));
	}
	
	function getCommand()
	{
		return $this->command;
	}
	
	function getArguments()
	{
		return $this->arguments;
	}
	
	function getJSONOutput()
	{
		return json_encode($this->getOutput());
	}
	
	function getOutput()
	{
		return $this->output;
	}
}
?>