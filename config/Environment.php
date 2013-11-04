<?php
class UserEnvironment{
	public static $config = array(
		"env" => Environment::DEV,
		"dev" => array(
			"dbConfig"	=> array(
				"host"		=> "127.0.0.1",
				"port"		=> "3306",
				"database"	=> "",
				"user"		=> "",
				"pass"		=> ""
			)
		),
		"production" => array(
			"dbConfig"	=> array(
				"host"		=> "127.0.0.1",
				"port"		=> "3306",
				"database"	=> "",
				"user"		=> "",
				"pass"		=> ""
			)
		),
		"test" => array(
			"dbConfig"	=> array(
				"host"		=> "127.0.0.1",
				"port"		=> "3306",
				"database"	=> "",
				"user"		=> "",
				"pass"		=> ""
			)
		)
	);
}
?>
