<?php

class BaseController{
	protected $__;
	protected $flash = array();
	protected $params = array();

	private $shortName;
	private $controllerName;
	private $reflectionClass;
	private $currentActionName;
	
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
		$this -> params = $_REQUEST;
	}
	
	public function __autoload($filename)
	{
		require Environment::$conf['modelDir'] . $filename . '.php';
	}

	public function runAction($action)
	{
		if(method_exists(get_class($this) , $action)){
			$this -> currentActionName = $action;
			$this -> $action();
		}else{
			throw new RouterException("Action ".$action." not defined!!" , RouterException::ACTION_UNDEFINED);
		}
	}

	protected function get_files()
	{
		$files = array();
		foreach($_FILES as $k => $f)
		{
			if(is_array($f["type"]))
			{
				$files[$k] = array();
				$length = count($f["name"]);
				for($i=0; $i<$length ; $i++)
				{
					$k_file = array();
					$k_file["name"]		= $f["name"][$i];
					$k_file["type"]		= $f["type"][$i];
					$k_file["tmp_name"]	= $f["tmp_name"][$i];
					$k_file["error"]	= $f["error"][$i];
					array_push($files[$k] , $k_file);
				}
			}else{
				$files[$k] = $f;
			}
		}
		return $files;
	}

	protected function get_params()
	{
		return $this -> params;
	}

	protected function getParams()
	{
		return $this -> params;
	}

	protected function getParam($key){
		return $this -> params[$key];
	}
	
	protected  function setVals($vals)
	{
		$this->re_vals = $vals;
	}
	protected function render($template = null)
	{
		//TODO: improve this variables accessed automatically by a sign of specific symbol
		$callerClass  = $this -> shortName . 'Views';
		$callerMethod = $this -> currentActionName;
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

	protected function echo_JSON($arr)
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
	
	protected function setFlash($flashMessage)
	{
		$this -> flash = array_merge($this -> flash , $flashMessage);
		$_SESSION['flash'] = $this -> flash;
	}
	
	private function unsetFlash()
	{
		$_SESSION['flash'] = null;
	}
	
	public function checkUserLogin()
	{
		$username = isset($_COOKIE['username'])?$_COOKIE['username']:"";
		$res =array();
		if(empty($username))
		{
			$res['ret'] = 0;
			$res['err_msg'] ="please login in";
		}
		return $res;
	}
	
	public function FormattedData($formatted_data)
	{
		foreach ($formatted_data as $key=>$value)
		{
			if(is_null($value))
			{
				$formatted_data[$key] = '';
			}
		}
		return $formatted_data;
	}
}
?>
