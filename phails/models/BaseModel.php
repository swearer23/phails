<?php
require("Validation.php");
require("Condition.php");
class BaseModel
{
	private static $adaptor;
	private static $columns;
	private static $model_name;
	protected static $table_name;
	protected $validation = array();
	protected $invalid_message;
	private $orm_object;

	public static function init()
	{
		self::$model_name = get_called_class();
		if(empty(self::$table_name))
		{
			self::$table_name = self::get_table_name();
		};
		self::$adaptor = new Adaptor(self::$table_name);
		self::$columns = self::$adaptor->get_columns(self::$table_name);
	}
	
	private static function get_table_name()
	{
		$pos = strpos(self::$model_name , 'Model');
		return Common::termToStr(substr(self::$model_name , 0 , $pos));
	}

	final public static function find($query_object=null)
	{
		return self::$adaptor->find($query_object);
	}

	final public static function find_by_id($id)
	{
		$query_object = array(
			"limit" => 1,
			"condition" => new Condition(
				when("id")->is($id)
			)
		);
		$project = self::find($query_object);
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
			$id = self::$adaptor->create($this->orm_object);
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
			$result = self::$adaptor->update($this->orm_object);
		}else{
			return false;
		}
	}

	final public function count($condition)
	{
		return self::$adaptor->count($condition);
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
		foreach(self::$columns as $c)
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
