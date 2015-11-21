<?php
ob_start();
date_default_timezone_set("Asia/Chongqing");
require '../phails/PhailStarter.php';
function __autoload($classname){
	Common::requireClass($classname);
}
$request = Common::getRequest();
session_start();
$controller = $request["controller"];
$action = $request["action"];
$class = new $controller($request);
$class->runAction($action);
ob_end_flush();
?>
