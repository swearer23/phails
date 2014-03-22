<?php
class RouterGenerator
{
	public static function insertMap($map)
	{
		$routerMap = RouterMap::$map;
		$routerMap = array_merge($routerMap , $map);
		self::writeIntoFile($routerMap);
	}
	
	private static function writeIntoFile($content)
	{
		$content = self::arrayToStr($content);
		$routerMapPath = Environment::$conf['routerDir'] . 'RouterMap.php';
		$routerMapTplPath = Environment::$conf['routerDir'] . 'RouterMap.tpl';
		$routerMapTpl = file_get_contents($routerMapTplPath);
		$routerMapTpl = str_replace('{{content}}' , $content , $routerMapTpl);
		file_put_contents($routerMapPath , $routerMapTpl);
	}
	
	private static function arrayToStr($array)
	{
		$str = '';
		foreach($array as $k => $v)
		{
			$str .= "'" . $k . "' => '" . $v . "', \n";
		}
		return $str;
	}
}
?>
