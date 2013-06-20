<?php
class Environment {
	public static $conf;
	public static $root;
	
	public static function init()
	{
		require 'Common.php';
		require dirname(__FILE__) . '/../exceptions/BaseException.php';
		require dirname(__FILE__) . '/../models/BaseModel.php';
		require dirname(__FILE__) . '/../controllers/BaseController.php';
		
		require dirname(__FILE__) . '/../router/RouterMap.php';
		require dirname(__FILE__) . '/../router/RouterGenerator.php';
		
		session_start();
		
		self::$conf = array(
			'root' 			=> Environment::$root,
			'modelDir'		=> Environment::$root . '/../app/models/',
			'controllerDir'	=> Environment::$root . '/../app/controllers/',
			'viewDir'		=> Environment::$root . '/../app/views/',
			'routerDir'		=> Environment::$root . '/../Phails/router/'
		);
	}
}
?>
