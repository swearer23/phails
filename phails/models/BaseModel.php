<?php
require("Validation.php");
require("Condition.php");
class BaseModel
{
	protected static $adaptor;
	protected static $columns;
	protected static $model_name;
	protected static $table_name;
	protected static $db_name;
	protected static $db_config;
	protected $validation = array();
	protected $invalid_message;
	protected $orm_object;

	public static function init()
	{
		static::$model_name = get_called_class();
		if(empty(static::$table_name))
		{
			static::$table_name = static::get_table_name();
		};
		static::$adaptor = new Adaptor(static::$model_name , static::$table_name , static::$db_name , static::$db_config);
		static::$columns = static::$adaptor->get_columns(static::$table_name);
	}
	
	private static function get_table_name()
	{
		$pos = strpos(static::$model_name , 'Model');
		return Common::termToStr(substr(static::$model_name , 0 , $pos));
	}

	final public static function find($query_object=null)
	{
		return static::$adaptor->find($query_object);
	}

	final public static function find_all_by_condition($condition)
	{
		if($condition instanceof Condition)
		{
			return static::find(array(
				"condition" => $condition
			));
		}else{
			throw new ORMException("parameter is not type of Condition in method find_all_by_condition" , ORMException::CONDITION_TYPE_ERROR);
		}
	}

	final public static function find_by_id($id)
	{
		$query_object = array(
			"limit" => 1,
			"condition" => new Condition(
				when("id")->is($id)
			)
		);
		$project = static::find($query_object);
		if(empty($project) || !is_array($project)){
			return null;
		}else{
			return $project[0];
		}
	}

	public function __construct($params = null)
	{
		if(!empty($params)){
			$this->orm_mapping($params);
		}
	}
	
	final public function create($object_array = null)
	{
		$this->orm_mapping($object_array);
		$validity = $this->validate();
		if($validity)
		{
			$id = static::$adaptor->create($this->orm_object);
			$this->id = $id;
			return true;
		}else{
			return false;
		}
	}

	final public function update($object_array = null)
	{
		$this->orm_mapping($object_array);
		$validity = $this->update_validate();
		if($validity)
		{
			$result = static::$adaptor->update($this->orm_object);
		}else{
			return false;
		}
	}

	final public function count($condition)
	{
		return static::$adaptor->count($condition);
	}

	public function to_array()
	{
		return $this->mapping_out();
	}

	public function get_validation()
	{
		return $this->validation;
	}

	public function get_invalid_message()
	{
		return $this->invalid_message;
	}

	public function set_invalid_message($invalid_message)
	{
		$this->invalid_message = $invalid_message;
	}

	private function orm_mapping($object_array)
	{
		if(!empty($object_array))
		{
			$this->mapping_in($object_array);
		}
		$this->mapping_out();
	}

	private function update_validate()
	{
		$validation = new Validation($this , Validation::UPDATE);
		return $validation->validate();
	}

	private function validate()
	{
		$validation = new Validation($this);
		return $validation->validate();
	}

	private function mapping_in($params)
	{
		foreach($params as $k=>$v)
		{
			$this->$k = $v;
		}
	}

	private function mapping_out()
	{
		$object_array = array();
		foreach(static::$columns as $c)
		{
			if(property_exists($this , $c))
			{
				$object_array[$c] = $this->$c;
			}else{
				$this->$c = null;
			}
		}
		$this->orm_object = $object_array;
		return $this->orm_object;
	}

}
?>
