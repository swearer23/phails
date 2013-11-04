<?php
/**
 * 异常基类
 * @author gaofeng
 *
 */
abstract class BaseException extends Exception{
	
	public static $code_mapping = array();
	
	public function __construct($message, $code=0, $previous=null){
		if(!is_numeric($code)){
			$code = 0;
		}
		parent::__construct('', $code, $previous);
		return $message;
		/*
		if(json_decode($message, true) == false){
			if(php_sapi_name() == 'cli'){
				$message = $this->getCliMessage($message);
			}else{
				$message = $this->getWebMessage($message);
			}
		}
		$this->message = $message;
		 */
	}

	private function getWebMessage($message){
		$info = array(
				'timestamp'=>date('YmdHis'),
				'message'=>$message,
				'file'=>$this->getFile(),
				'line'=>$this->getLine(),
				'code'=>$this->getCode(),
				'args'=>array(
						'url'=>$_SERVER['REQUEST_URI'],
						'refer_url'=>isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'',
						'get'=>$_GET,
						'post'=>$_POST,
				),
		);
		return json_encode($info);
	}

	private function getCliMessage($message){
		$info = array(
				'timestamp'=>date('YmdHis'),
				'message'=>$message,
				'file'=>$this->getFile(),
				'line'=>$this->getLine(),
				'code'=>$this->getCode(),
				'args'=>array(
						'file'=>$_SERVER['PHP_SELF'],
						'argv'=>$_SERVER['argv'],
				),
				'context'=>SysEnv::$context,
		);
		return json_encode($info);
	}

}
