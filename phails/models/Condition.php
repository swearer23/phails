<?php

require("ConditionElement.php");

function when($key_name){
	return new ConditionElement($key_name);
}

class Condition
{
	public $condition_statement;
	private $condition_elements;
	private $condition_statements_array = array();

	public function __construct()
	{
		$condition_elements = func_get_args();
		foreach($condition_elements as $ce)
		{
			if($ce instanceof ConditionElement)
			{
				continue;
			}else{
				throw new ORMException("model received condition in incorrect type, one or more of condition is not type of ConditionElement!!" , ORMException::CONDITION_ELEMENT_ERROR);
			}
		}
		$this->condition_elements = $condition_elements;
		$this->condition_statement = $this->generate_where_statement();
	}

	private function generate_where_statement()
	{
		foreach($this->condition_elements as $ce)
		{
			if($ce->get_statement()){
				array_push($this->condition_statements_array , $ce->get_statement());
			}
		}
		$statement = implode($this->condition_statements_array, " AND ");
		if(empty($statement))
		{
			return null;
		}else{
			return " WHERE ".$statement;
		}
	}
}
?>
