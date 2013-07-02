<?php
abstract class Command
{
	abstract function run($parameters);
	abstract function help();
	
	function color($string)
	{
		return $string;
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
	
	function missingKeys($result, $compareArray)
	{
		$missing = array();
		$compLen = count($compareArray);
		
		for ($i = 0; $i < $compLen; $i++)
		{
			if ($compareArray[$i]{0} == '-')
			{
				$compareArray[$i] = substr($compareArray[$i], 1);
			}
		}
		
		foreach ($result as $oKey => $oVal)
		{
			foreach ($compareArray as $cVal)
			{
				if (strcasecmp($oKey, $cVal) == 0)
				{
					break;
				}
			}
			
			$missing[] = $oKey;
		}
		
		if (count($missing) == 0)
		{
			return false;
		}
		
		return $missing;
	}
	
	function parseFlaggedParameters($noopt = array(), $params)
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
				
				if (!in_array($pname, $noopt) && $value === true
					&& $nextparm !== false && $nextparm{0} != '-')
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
}
?>