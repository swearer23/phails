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
		$this->statement = $this->key_name . "='" . $value . "'";
		return $this;
	}

	public function is_not($value)
	{
		$this->statement = $this->key_name . "<>'" . $value . "'";
		return $this;
	}

	public function get_statement()
	{
		return $this->statement;
	}
}
?>
