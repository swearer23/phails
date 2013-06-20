<?php
class BaseException extends Exception
{
	public static function controllerNotFound($controller)
	{
		throw new Exception("controller $controller does not exist");
	}
	
	public static function missingParams($param)
	{
		throw new Exception("missing params $param");
	}
}
?>
