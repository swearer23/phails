<?php
require '../phails/PhailStarter.php';
function __autoload($classname){
	Common::requireClass($classname);
}
$request = Common::getRequest();
$controller = $request["controller"];
$action = $request["action"];
$class = new $controller($request);
$class->$action();
?>
