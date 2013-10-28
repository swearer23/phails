<?php
class Common
{
	public static function getRequest()
	{
		$controller = $_GET["controller"];
		if(!$controller)
		{
			BaseException::controllerNotFound($controller);
		}
		$controller = self::strToTerm($controller , 'controller');
		$action = $_GET["action"];
		$action = self::strToTerm($action , 'action');
		$request = array("controller" => $controller , "action" => $action);
		return $request;
	}
	
	public static function getParams()
	{
	
	}
	
	public static function strToTerm($str , $suffix = '')
	{
		$str = str_replace("_" , " " , $str);
		$str = ucwords($str);
		$str = str_replace(" " , "" , $str);
		switch($suffix)
		{
			case 'controller':
			{
				$suffix = ucwords($suffix);
				$str =	$str.$suffix;
			};break;
			case 'action':
			{
				$firstChar = substr($str , 0 , 1);
				$firstChar = strtolower($firstChar);
				$str = $firstChar . substr($str , 1 , strlen($str));
			};break;
			default:
			{
				$str =	$str;
			};break;
		}
		return $str;
	} 
	
	public static function termToStr($term)
	{
		$map = RouterMap::$map;
		if(isset($map[$term]))
		{
			return $map[$term];
		}else{
			$str = preg_replace('/[A-Z]/e' ,  "_.strtolower($0)" , $term);
			if($str[0] == '_')
			{
				$str = substr($str , 1 , strlen($str)-1);
			}
			RouterGenerator::insertMap(array($term => $str));
			return $str;
		}
	}
	
	public static function requireClass($classname)
	{
		if(strpos($classname , 'Controller'))
		{
			require Environment::$conf['controllerDir'].$classname.'.php';
			return;
		}
		if(strpos($classname , 'Model'))
		{
			require Environment::$conf['modelDir'].$classname.'.php';
			return;
		}
	}
	
	public function getCallerMethod()
	{
		$backtrace = debug_backtrace();
		$lastStack = array_pop($backtrace);
		return $lastStack['function'];
	}
}
?>
