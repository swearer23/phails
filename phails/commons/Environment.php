<?php
class Environment {

	public static $conf;
	private static $root;
	private static $userENV;

	const DEV = "dev";
	const PRODUCTION = "production";
	const TEST = "test";

	private static $running_env;

	public static function init() {
        self::$root = getcwd() . "/../";
        $GLOBALS['root'] = self::$root;

		require 'Common.php';
		require dirname(__FILE__) . '/../exceptions/BaseException.php';
		require dirname(__FILE__) . '/../exceptions/ORMException.php';
		require dirname(__FILE__) . '/../exceptions/DbException.php';
		require dirname(__FILE__) . '/../exceptions/RouterException.php';
		require dirname(__FILE__) . '/../dbAdaptors/MysqlAdaptor/Adaptor.php';
		require dirname(__FILE__) . '/../models/BaseModel.php';
		require dirname(__FILE__) . '/../controllers/BaseController.php';
		require dirname(__FILE__) . '/../controllers/RenderProcessor.php';
		require dirname(__FILE__) . '/../router/RouterMap.php';
		require dirname(__FILE__) . '/../router/RouterGenerator.php';

        self::$conf = array(
            'root' => Environment::$root,
            'modelDir' => Environment::$root . 'app/models/',
            'controllerDir' => Environment::$root . 'app/controllers/',
            'viewDir' => Environment::$root . 'app/views/',
            'utilsDir' => Environment::$root . 'app/utils/',
            'routerDir' => Environment::$root . 'phails/router/',
            'configDir' => Environment::$root . 'config/',
            'wwwDir' => Environment::$root . 'www/',
            "classLoaders" => array(
                "*Model" => array(
                    "path"                  => "app/models/",
                    "skip"                  => array("BaseModel"),
                    "afterLoadCallback"     => array("&", "init"),
                ),
                "*Controller" => array(
                    "path"                  => "app/controllers/",
                    "skip"                  => array("BaseController"),
                ),
                "*" => array(
                    "path"                  => "app/utils/"
                )
            )
        );
    }

	public static function includeUserENV() {
		require dirname(__FILE__) . '/../../config/Environment.php';

		self::$userENV = UserEnvironment::$config;
		self::$running_env = self::$userENV["env"];
        if (isset(self::$userENV["all"])) {
            foreach (self::$userENV["all"] as $key => $value) {
                self::$userENV[self::$running_env][$key] = $value;
            }
        }
		foreach (self::$userENV[self::$running_env] as $k => $v) {
            if ($k == "classLoaders") {
                $v = array_merge(self::$conf[$k], $v);
            }
			self::$conf[$k] = $v;
		}
	}
}
?>
