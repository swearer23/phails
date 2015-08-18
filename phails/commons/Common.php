<?php
require_once 'ClassLoader.php';
require_once 'ParameterWrapperInterface.php';
require_once 'ParameterWrapper.php';
require_once 'HttpRequest.php';

class Common
{
	public static function getRequest()
	{
		if(isset($_GET["controller"]))
		{
			$controller = self::strToTerm($_GET["controller"] , 'controller');
		}else{
			$controller = self::strToTerm("index" , "controller");
		}
		if(isset($_GET["action"])){
			if(method_exists($controller , $_GET["action"]))
			{
				$action = $_GET["action"];
			}else{
				$action = self::strToTerm($_GET["action"] , 'action');
			}
		}else{
			$action = self::strToTerm('index' , 'action');
		}
		$request = array("controller" => $controller , "action" => $action);
		$request = new HttpRequest($request);
		return $request;
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
            case 'component':
            {
                $suffix = ucwords($suffix);
                $str = $str.$suffix;
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
			$str = preg_replace_callback('/[A-Z]/' ,  array("Common" , "lowerFirstChar") , $term);
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
        /**
         * 尝试运行ClassLoader来进行class的加载
         */
        $classLoader = ClassLoader::getDefaultLoader();
        if ($classLoader->loadClass($classname)) {
            return;
        }

        /**
         * ClassLoader未能加载class后，使用旧方式来加载class
         */
		if(strpos($classname , 'Controller') && strrpos($classname , 'Controller') == strlen($classname) - strlen('Controller'))
		{
			require Environment::$conf['controllerDir'].$classname.'.php';
			return;
		}
		if(strpos($classname , 'Model') && strrpos($classname , 'Model') == strlen($classname) - strlen('Model'))
		{
			require Environment::$conf['modelDir'].$classname.'.php';
			$classname::init();
			return;
		}
		require Environment::$conf['utilsDir'].$classname.'.php';
		return;
	}

	public static function getCallerMethod()
	{
		$backtrace = debug_backtrace();
		$lastStack = array_pop($backtrace);
		return $lastStack['function'];
	}

	public static function lowerFirstChar($match)
	{
		return "_" . strtolower($match[0]);
	}
}
?>
