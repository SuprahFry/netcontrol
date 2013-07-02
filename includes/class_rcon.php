<?php
define('RCON_AUTH', 3);
define('RCON_ECOMMAND', 2);

class RCONSocket
{
	protected $host = '127.0.0.1';
	protected $socket = null;
	protected $port = 25575;
	protected $timeout = 0;
	protected $id = 0;
	
	function RCONSocket($host, $port = 25575)
	{
		$this->host = $host;
		$this->port = $port;
		$this->timeout = time();
	}
	
	function connect()
	{
		$this->socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		$connected = @socket_connect($this->socket, $this->host, $this->port);
		
		if ($connected)
		{
			socket_set_nonblock($this->socket);
			socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 1, 'usec' => 1));
		}
		
		return $connected;
	}
	
	function login($password = '')
	{
		if ($this->write(RCON_AUTH, $password) === false)
		{
			return false;
		}
		
		$response = $this->readPackets();
		
		if ($response === false)
		{
			return false;
		}
		
		return !isset($response[-1]);
	}
	
	function write($command, $arg1 = '')
	{
		global $settings;
		$id = ++$this->id; // data is in the format of id, command, argument, null char, nothing, null char
		$data = pack('VV', $id, $command).$arg1.chr(0).chr(0);
		$data = pack('V', strlen($data)).$data; // pack again for the size appended at the start
		@socket_set_block($this->socket);
			
		if (@socket_send($this->socket, $data, strlen($data), 0) === false)
		{
			return false;
		}
		
		@socket_set_nonblock($this->socket);
		$this->timeout = time();
		return $id;
	}
	
	function readPackets($block = true)
	{
		global $settings;
		$toReturn = array();
		$found = false;
		$size = '';
		
		while (!$found)
		{
			if ($size = @socket_read($this->socket, 4))
			{
				$found = true;
				break;
			}
			
			if (time() - $this->timeout >= $settings['rcon']['timeout'])
			{
				return true;
			}
			
			if (!$block && ($size == '' || $size === false))
			{
				return false;
			}
		}
		
		$size = unpack('V1size', $size);
		
		if ($size['size'] > 4096)
		{
			for ($i = 0; $i < 8; $i++)
			{
				$packet .= chr(0);
			}
			
			$result = @socket_read($this->socket, 4096);
			
			if ($result === false)
			{
				return false;
			}
			
			$packet .= $result;
		}
		else
		{
			$packet = @socket_read($this->socket, $size['size']);
			
			if ($packet === false)
			{
				return false;
			}
		}
		
		$this->timeout = time();
		$unpacked = unpack("V1id/V1response/a*s1/a*s2", $packet);
		$toReturn[$unpacked['id']] = $unpacked;
		return $toReturn;
	}
	
	function read()
	{
		$toReturn = array();
		$block = true;
		
		while (($packets = $this->readPackets($block)) !== false)
		{
			if ($packets === true)
			{
				return false;
			}
			
			foreach ($packets as $packet)
			{
				if (isset($toReturn[$packet['id']]))
				{
					$toReturn[$packet['id']]['s1'] .= $packet['s1'];
					$toReturn[$packet['id']]['s2'] .= $packet['s2'];
				}
				else
				{
					$toReturn[$packet['id']] = $packet;
				}
			}
			
			if (!empty($packets))
			{
				$block = false;
			}
		}
		
		return $toReturn;
	}
	
	function command($argument)
	{
		return $this->write(RCON_ECOMMAND, $argument);
	}
	
	function commandGetResponse($argument)
	{
		global $settings;
		$commandID = $this->command($argument);
		
		if ($commandID === false)
		{
			return false;
		}
		
		$toReturn = $this->read();
		
		if ($toReturn === false)
		{
			return false;
		}
		
		return ($settings['rcom']['quirks'] ? $toReturn[0] : $toReturn[$commandID]);
	}
	
	function disconnect()
	{
		@socket_close($this->socket);
	}
	
	function getError()
	{
		return @socket_last_error($this->socket);
	}
	
	function getErrorString()
	{
		return @socket_strerror($this->getError());
	}
}
?>