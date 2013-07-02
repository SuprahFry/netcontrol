<?php
class Quake3
{
	function queryServer($host, $port, $query)
	{
		if(!$fp = fsockopen('udp://'.$host, $port, $errno, $errstr, 5))
		{
			return false;
		}
		else
		{
			fwrite($fp, str_repeat(chr(255), 4).$query);
			socket_set_timeout($fp, 1);
			$response = '';
			
			while(true)
			{
				if(strlen($in = fread($fp, 1024)) == 0)
				{
					break;
				}
				
				$response .= $in;
			}
			
			fclose($fp);
			return str_replace(str_repeat(chr(255), 4), '', $response);
		}
	}
	
	function queryMasterList($host, $port, $query)
	{
		//$masterquery could be something like: getservers 69 full empty
		if(!$data = $this->queryServer($host,$port, $query))
		{
			return false;
		}
		
		$data = str_replace(array('getserversResponse', 'EOT'), '', $data);
		$data = explode('\\', $data);
		
		if(is_array($data) && ($count = count($data)) > 0)
		{
			$master_info = array();
			
			for($i = 0,$j = 0; $i < $count; $i++)
			{
				if(strlen($data[$i]) === 6)
				{
					$master_info[$j]['ip'] = ord($data[$i][0]).'.'.ord($data[$i][1]).'.'.ord($data[$i][2]).'.'.ord($data[$i][3]);
					$tmp_port = unpack('nint', substr($data[$i], 4, 2));
					$master_info[$j]['port'] = (int)$tmp_port['int'];
					$j++;
				}
			}
			
			return $master_info;
		}
		
		return false;
	}
	
	function queryServerStatus($host, $port)
	{
		if(!$status['response'] = $this->queryServer($host, $port, 'getstatus'))
		{
			return false;
		}
		
		$data = explode("\n",$status['response']);
		
		if(is_array($data) && ($count_d = count($data)) > 1)
		{
			$arr_info = array_chunk(explode("\\", substr($data[1], 1)), 2);
			$count_i = count($arr_info);
			
			for($i = 0; $i < $count_i; $i++)
			{
				$status['info'][htmlspecialchars(strtolower($arr_info[$i][0]))] = htmlspecialchars($arr_info[$i][1]);
			}
			
			for($i = 2,$j = 0; $i < $count_d; $i++)
			{
				if(!empty($data[$i]) && is_array($arr = explode(' ', $data[$i])))
				{
					$status['players'][$j]['score'] = (isset($arr[0])) ? $arr[0] : '-1';
					$status['players'][$j]['ping'] = (isset($arr[1])) ? $arr[1] : '-1';
					
					if(isset($status['info']['protocol']) && $status['info']['protocol']==69)
					{
						$status['players'][$j]['team'] = (isset($arr[2])) ? $arr[2] : ''; // 0=free, 1=red, 2=blue, 3=spectator
						$status['players'][$j]['isbot'] = (isset($arr[3])) ? $arr[3] : '';					
					}
					
					$status['players'][$j]['name'] = substr($data[$i], strpos($data[$i], '"') + 1, (strrpos($data[$i], '"') - strpos($data[$i], '"')) - 1);
					$j++;
				}
			}
			
			return $status;
		}
		
		return false;
	}
	
	function queryServerInfo($host, $port)
	{
		if(!$info_response = $this->queryServer($host, $port, 'getinfo'))
		{
			return false;
		}
		
		if(is_array($data = explode("\x0a", $info_response)) && count($data) > 1)
		{
			$arr = array_chunk(explode("\\", substr($data[1], 1)), 2);
			$count = count($arr);
			
			for($i = 0; $i < $count; $i++)
			{
				$info[htmlspecialchars(strtolower($arr[$i][0]))] = htmlspecialchars($arr[$i][1]);
			}
			
			return $info;
		}
		
		return false;
	}
}
?>