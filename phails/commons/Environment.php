<?php
class Environment {
	public static $conf;
	public static $root;

	private static $userENV;

	const DEV = "dev";
	const PRODUCTION = "production";
	const TEST = "test";

	private static $running_env;
	
	public static function init()
	{
		require 'Common.php';
		require dirname(__FILE__) . '/../exceptions/BaseException.php';
		require dirname(__FILE__) . '/../exceptions/ORMException.php';
		require dirname(__FILE__) . '/../exceptions/DbException.php';
		require dirname(__FILE__) . '/../exceptions/RouterException.php';
		require dirname(__FILE__) . '/../dbAdaptors/MysqlAdaptor/Adaptor.php';
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
			'routerDir'		=> Environment::$root . '/../phails/router/'
		);
	}

	public static function includeUserENV(){
		require dirname(__FILE__) . '/../../config/Environment.php';
		self::$userENV = UserEnvironment::$config;
		self::$running_env = self::$userENV["env"];
		self::$conf["db_config"] = self::$userENV[self::$running_env]["dbConfig"];
	}
}
?>
