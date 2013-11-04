<?php
require dirname(__FILE__) . '/commons/Environment.php';
Environment::$root = getcwd();
Environment::init();
Environment::includeUserENV();
?>
