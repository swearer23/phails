<?php
class Validation{
	const UPDATE = 0;
	const UNIQUE = 10;
	const NECESSARY = 20;

	private static $validation_type;
	private $model;
	private $validation;

	public function __construct($model , $validation_type = null)
	{
		$this->model = $model;
		self::$validation_type = $validation_type;
	}

	public function validate()
	{
		$valid = true;
		$this->validation = $this->model->get_validation();
		foreach($this->validation as $v)
		{
			switch($v["type"]){
				case self::UNIQUE:
				{
					$valid = $this->uniq_validation($v["column"] , $this->model->$v["column"]);
					break;
				}
				case self::NECESSARY:
				{
					$valid = $this->necessary_validation($v["column"] , $this->model->$v["column"]);
					break;
				}
				default:
				{	
					if(self::$validation_type === self::UPDATE && isset($v["skip_update"]) && $v["skip_update"]){
					}else{
						$method = $v["type"];
						$valid = $this->model->$method();
					}
				}
			}
			if(!$valid){
				if(isset($v["message"])){
					$this->model->set_invalid_message($v["message"]);
				}
				if(isset($v["error_code"])){
					$this->model->set_error_code($v["error_code"]);
				}
				break;
			}
		}
		return $valid;
	}

	private function uniq_validation($column , $value)
	{
		if(self::$validation_type === self::UPDATE)
		{
			$condition = new Condition(
				when($column)->is($value),
				when("id")->is_not($this->model->id)
			);
		}else{
			$condition = new Condition(
				when($column)->is($value)
			);
		}
		$count = $this->model->count(array(
			"condition" => $condition
		));
		if($count > 0){
			return false;
		}else{
			return true;
		}
	}

	private function necessary_validation($column , $value)
	{
		if(empty($value) && $value !== 0 && $value !== "0")
		{
			return false;
		}
		return true;
	}
}
?>
