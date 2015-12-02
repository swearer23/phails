<?php

class BaseController{
	protected $flash = array();
	protected $params = array();
    protected $request = null;
	protected $before_filter = array();

	private $shortName;
	private $controllerName;
	private $reflectionClass;
	private $currentActionName;

	public function __construct(ParameterWrapper $request)
	{
		if(isset($_SESSION['flash']))
		{
			$this -> flash = $_SESSION['flash'];
			$this -> unsetFlash();
		}
		$this -> reflectionClass = new ReflectionClass($this);
		$this -> controllerName = $this -> reflectionClass -> getName();
		$this -> getShortName();
		unset($_REQUEST["controller"]);
		unset($_REQUEST["action"]);
        //request对象
        $this->request = $request;
        $this->request->add($_REQUEST);
        $this->request->add($_COOKIE);
        //适应原有代码
        $this->params =& $this->request;
	}

	public function __autoload($filename)
	{
		require Environment::$conf['modelDir'] . $filename . '.php';
	}

	public function runAction($action)
	{
		if(method_exists(get_class($this) , $action)){
			$this -> currentActionName = $action;
			$this -> before_filter();
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

	/*
	protected  function setVals($vals)
	{
		$this->re_vals = $vals;
	}
	 */

	protected function render($render_params = null)
	{
		$template = isset($render_params["template"]) ? $render_params["template"] : null;
		$params = isset($render_params["params"]) ? $render_params["params"] : null;
		if (!isset($this->currentActionName) || empty($this->currentActionName) ) {
			$trace=debug_backtrace();
			$caller=$trace[1];
			$this->currentActionName = $caller['function'];
		}

		//TODO: improve this variables accessed automatically by a sign of specific symbol
		$callerClass  = $this -> shortName . 'Views';
		$callerMethod = $this -> currentActionName;
		if(!$template)
		{
			$template = Environment::$conf['viewDir'] . $callerClass . '/' . $callerMethod . '.php';
		}else{
			$template = Environment::$conf['viewDir'] . $template;
		}
		$renderProcessor = new RenderProcessor();
		$renderProcessor->render($template , $params);
	}

	protected function echoJSON($arr){
		$json = json_encode($arr);
		header('Content-type: application/json;charset=utf-8');
		echo $json;
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
				$controller = Common::termToStr($to['controller']);
			}else{
				$controller = Common::termToStr($this -> shortName);
			}
			$action 	= Common::termToStr($to['action']);
			$params		= '';
			foreach($to['params'] as $k => $v)
			{
				$params .= '&'.$k.'='.$v;
			}
			$url = '/index.php?controller=' . $controller;
			if (isset($action) && strlen(trim($action)) > 0) {
				$url .= '&action='.$action;
			}
			$url .= $params;
		}else{
			$url = $to;
		}
		header('Location:' . $url);
		die;
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

    protected function set_flash($flash_message){
		$this -> flash = array_merge($this -> flash , $flash_message);
		$_SESSION['flash'] = $this -> flash;
	}

	protected function get_flash($flash_key){
		return isset($this->flash[$flash_key]) ? $this->flash[$flash_key] : null;
	}

	private function unsetFlash()
	{
		$_SESSION['flash'] = null;
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

	protected function before_filter() {
		foreach ($this->before_filter as $filter) {
			$filter_action = '';
			if ( isset($filter['only']) && !empty($filter['only']) ) {
				if ( in_array($this->currentActionName, $filter['only']) ) {
					if ( isset($filter['action']) && !empty($filter['action']) ) {
						$filter_action = $filter['action'];
					}
				}
			} else {
				$skip_actions = array();
				if ( isset($filter['skip']) && !empty($filter['skip']) ) {
					$skip_actions = $filter['skip'];
				}
				if ( in_array($this->currentActionName, $skip_actions) == false ) {
					if ( isset($filter['action']) && !empty($filter['action']) ) {
						$filter_action = $filter['action'];
					}
				}
			}

			if (!empty($filter_action) && $filter_action != $this->currentActionName) {
				if ( method_exists(get_class($this) , $filter_action) ) {
					$this->$filter_action();
				} else {
					throw new RouterException("Action ".$filter_action." not defined!!" , RouterException::ACTION_UNDEFINED);
				}
			}
		}
	}

	protected function get_controller_name() {
		return $this->controllerName;
	}

	protected function get_action_name() {
		return $this->currentActionName;
	}
}
?>
