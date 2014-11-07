<?php
class ConditionElement
{
	private $key_name;
	private $statement;

	public function __construct($key_name)
	{
		$this->key_name = $key_name;
	}

	public function is($value)
	{
		if($value){
			$this->statement = $this->key_name . "='" . $value . "'";
		}else{
			$this->statement = null;
		}
		return $this;
	}

	public function is_not($value)
	{
		$this->statement = $this->key_name . "<>'" . $value . "'";
		return $this;
	}

	public function between($lower_value , $greater_value)
	{
		$this->statement = $this->key_name . ">='" . $lower_value . "' AND " . $this->key_name . "<='" . $greater_value . "'";
		return $this;
	}

	public function within($lower_value , $greater_value)
	{
		$this->statement = $this->key_name . ">'" . $lower_value . "' AND " . $this->key_name . "<'" . $greater_value . "'";
		return $this;
	}

	public function in($elements_array)
	{
		$in = implode("','" , $elements_array);
		$this->statement = $this->key_name . " in ('" . $in . "')";
		return $this;
	}

	public function not_in($elements_array)
	{
		$notIn = implode("','" , $elements_array);
		$this->statement = $this->key_name . " not in ('" . $notIn . "')";
		return $this;
	}

	public function gt($value)
	{
		if($value){
			$this->statement = $this->key_name . ">'" . $value . "'";
		}else{
			$this->statement = null;
		}
		return $this;
	}

	public function lt($value)
	{
		if($value){
			$this->statement = $this->key_name . "<'" . $value . "'";
		}else{
			$this->statement = null;
		}
		return $this;
	}

	public function gteq($value)
	{
		$this->statement = $this->key_name . ">='" . $value . "'";
		return $this;
	}

	public function lteq($value)
	{
		$this->statement = $this->key_name . "<='" . $value . "'";
		return $this;
	}

	public function get_statement()
	{
		return $this->statement;
	}
}
?>
