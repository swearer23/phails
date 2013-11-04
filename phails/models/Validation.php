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
		foreach($this->validation as $k=>$v)
		{
			switch($v["type"]){
				case self::UNIQUE:
				{
					$valid = $this->uniq_validation($k , $this->model->$k);
					break;
				}
				case self::NECESSARY:
				{
					$valid = $this->necessary_validation($k , $this->model->$k);
					break;
				}
			}
			if(!$valid){break;}
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
			$this->model->set_invalid_message($this->validation[$column]["message"]);
			return false;
		}else{
			return true;
		}
	}

	private function necessary_validation($column , $value)
	{
		if(empty($value))
		{
			$this->model->set_invalid_message($this->validation[$column]["message"]);
			return false;
		}
		return true;
	}
}
?>
