<?php

class BaseController{
	protected $__;
	protected $flash = array();

	private $shortName;
	private $controllerName;
	private $reflectionClass;
	
	public function __construct()
	{
		if(isset($_SESSION['flash']))
		{
			$this -> flash = $_SESSION['flash'];
			$this -> unsetFlash();
		}
		$this -> reflectionClass = new ReflectionClass($this);
		$this -> controllerName = $this -> reflectionClass -> getName();
		$this -> getShortName();
	}
	
	public function __autoload($filename)
	{
		require Environment::$conf['modelDir'] . $filename . '.php';
	}
	
	public function render($template = null)
	{
		//TODO: improve this variables accessed automatically by a sign of specific symbol
		$callerClass  = $this -> shortName . 'View';
		$callerMethod = $this -> getCaller();
		if(!$template)
		{
			$template = Environment::$conf['viewDir'] . $callerClass . '/' . $callerMethod . '.php';
		}else{
			$template = Environment::$conf['viewDir'] . $template;
		}
		ob_start();
		$__ = $this -> __;
		include($template);
		$page = ob_get_clean();
		echo $page;
	}
	
	public function echoJson($arr)
	{
		$json = json_encode($arr);
		header('Content-type: application/json;charset=utf-8');
		echo $json;
	}
	
	protected function redirect($to)
	{
		if(is_array($to))
		{
			if(!isset($to['params']))
			{
				$to['params'] = array();
			}
			if(isset($to['controller']))
			{
				$controller = Common::StrToTerm($to['controller'] , 'controller');
			}else{
				$controller = Common::termToStr($this -> shortName);
			}
			$action 	= Common::termToStr($to['action']);
			$params		= '';
			foreach($to['params'] as $k => $v)
			{
				$params .= '&'.$k.'='.$v;
			}
			$url = '/index.php?controller=' . $controller . '&action=' . $action . $params;
		}else{
			$url = $to;
		}
		Header('Location:' . $url);
		exit;
	}
	
	private function getShortName()
	{
		$pos = strpos($this->controllerName , 'Controller');
		$this -> shortName = substr($this -> controllerName , 0 , $pos);
	}
	
	private function getCaller()
	{
		$caller = Common::getCallerMethod();
		return $caller;
	}
	
	protected function setFlash($flashMessage)
	{
		$this -> flash = array_merge($this -> flash , $flashMessage);
		$_SESSION['flash'] = $this -> flash;
	}
	
	private function unsetFlash()
	{
		$_SESSION['flash'] = null;
	}
}
?>
