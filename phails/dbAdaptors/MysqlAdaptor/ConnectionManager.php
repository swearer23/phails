<?php
class ConnectionManager extends PDO{

	public static $conn;

	public static function getInstance($db_name = null , $use_config = null){
		if(empty($use_config))
		{
			$config = Environment::$conf["dbConfig"];
		}else{
			$config = $use_config;
		}
		if(empty($config)){
			throw new DbException('empty config', DbException::PARAMS_ERROR);
		}
		if(!empty($db_name))
		{
			$config["database"] = $db_name;
		}
		if(empty(self::$conn)){
			self::$conn = self::init($config);
		}
		// 连接超时的处理 add by gen 20121212
		$status = self::$conn ->getAttribute(PDO::ATTR_SERVER_INFO);
		if($status == 'MySQL server has gone away' || $status == '2006 MySQL server has gone away')
		{   
			self::$conn = null;
			self::$conn = self::init($config);
		}  
		return self::$conn;
	}

	public static function reconnect(){
		$config = Environment::$conf["db_config"];
		if(empty($config)){
			throw new DbException('empty config', DbException::PARAMS_ERROR);
		}
		self::$conn = self::init($config);
		return self::$conn;
	}

	private static function init($config){
		try{
			$conn = null;
			$dsn = 'mysql:host='.$config['host'].';port='.$config['port'].';dbname='.$config['database'];
			$options = array(
				PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
			);
			if(isset($config['persistent'])){
				$options =  array(PDO::ATTR_PERSISTENT => $config['persistent']);
			}
			$conn = new ConnectionManager($dsn, $config['user'], $config['pass'], $options);
			$conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
		}catch(Exception $e){
			throw $e;
		}
		return $conn;
	}
}
?>
