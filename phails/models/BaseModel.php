<?php
require("Validation.php");
require("Condition.php");
class BaseModel
{
	protected static $adaptor_cache = array();
	protected static $column_cache = array();
	protected static $model_cache = array();
	protected static $table_cache = array();
	protected static $db_name;
	protected static $db_config;
	protected $validation = array();
	protected $invalid_message;
	protected $error_code;
	protected $orm_object;

	public static function init()
	{
		// reset model_name & table_name in one request
		$modelname = get_called_class();
		self::set_model($modelname);
		self::set_table($modelname);
		self::set_adaptor($modelname);
		self::set_columns($modelname);
	}

	protected static function set_model($model_name)
	{
		if(!isset(self::$model_cache[$model_name]))
		{
			self::$model_cache[$model_name] = $model_name;
		}
	}

    final static public function update_records($object_array)
    {
        $adaptor = self::get_adaptor();
        $result = $adaptor->update_records($object_array);
        return $result;
    }

    final static public function drop_records($object_array)
    {
        $adaptor = self::get_adaptor();
        $result = $adaptor->drop_records($object_array);
        return $result;
    }
    /**
     * 直接执行一条dql语句
     */
    final public static function query($sql, $array = null) {
        $adaptor = self::get_adaptor ();
        return $adaptor->query ( $sql, $array );
    }
    /**
     * 直接执行一条dml语句
     */
    final public static function execute($sql, $array = null) {
        $adaptor = self::get_adaptor ();
        return $adaptor->execute ( $sql, $array );
    }


    public static function get_model()
	{
    	$class_name = get_called_class();
		return self::$model_cache[$class_name];
	}

	protected static function set_table($model_name)
	{
		$pos = strpos($model_name , 'Model');
		$table_name = Common::termToStr(substr($model_name , 0 , $pos));
		if(!array_key_exists($table_name , self::$table_cache))
		{
			self::$table_cache[$model_name] = $table_name;
		}
	}

	protected static function get_table()
	{
		$class_name = get_called_class();
		return self::$table_cache[$class_name];
	}

	protected static function set_adaptor($model_name)
	{
		if(!array_key_exists($model_name , self::$adaptor_cache))
		{
			self::$adaptor_cache[$model_name] = new Adaptor(self::get_model($model_name) , self::get_table($model_name) , static::$db_name , static::$db_config);
        }
	}

	protected static function get_adaptor()
	{
		$class_name = get_called_class();
		return self::$adaptor_cache[$class_name];
	}

	protected static function set_columns($model_name)
	{
		if(!array_key_exists($model_name , self::$column_cache))
		{
			$adaptor = self::get_adaptor($model_name);
			$table_name = self::get_table($model_name);
			self::$column_cache[$model_name] = $adaptor->get_columns($table_name);
		}
	}

	protected static function get_columns()
	{
		$class_name = get_called_class();
		return self::$column_cache[$class_name];
	}

	final public static function find($query_object=null)
	{
		$adaptor = self::get_adaptor();
		return $adaptor->find($query_object);
	}

	final public static function find_one($query_object=null)
	{
		$adaptor = self::get_adaptor();
		return $adaptor->findOne($query_object);
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

	protected function beforeCreate(){};
	protected function beforeUpdate(){};
	protected function beforeSave(){};
	protected function afterCreate(){};
	protected function afterUpdate(){};
	protected function afterSave(){};
	protected function before_create(){}
	protected function before_update(){}
	protected function before_save(){}
	protected function after_create(){}
	protected function after_save(){}
	protected function after_update(){}

	final public function create($object_array = null)
	{
		$this->orm_mapping($object_array);
		$this->beforeCreate();
		$this->beforeSave();
		$this->before_create();
		$this->before_save();
		$this->orm_mapping();
		$validity = $this->validate();
		if($validity)
		{
			$adaptor = self::get_adaptor();
			$id = $adaptor->create($this->orm_object);
			$this->id = $id;
			$this->afterCreate();
			$this->afterSave();
			$this->after_create();
			$this->after_save();
			return true;
		}else{
			return false;
		}
	}

	final protected function update($object_array = null)
	{
		$this->orm_mapping($object_array);
		$this->beforeUpdate();
		$this->beforeSave();
		$this->before_update();
		$this->before_save();
		$this->orm_mapping();
		$validity = $this->update_validate();
		if($validity)
		{
			$adaptor = self::get_adaptor();
			$result = $adaptor->update($this->orm_object);
			$this->afterUpdate();
			$this->afterSave();
			$this->after_update();
			$this->after_save();
			return $result;
		}else{
			return false;
		}
	}

	final static public function count($condition)
	{
		$adaptor = self::get_adaptor();
		return $adaptor->count($condition);
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

	public function get_error_code()
	{
		return $this->error_code;
	}

	public function set_error_code($error_code)
	{
		$this->error_code = $error_code;
	}

	public function destroy($object_array = null)
	{
		$this->orm_mapping($object_array);
		$adaptor = self::get_adaptor();
		$result = $adaptor->destroy($this->orm_object);
		return $result;
	}

	private function orm_mapping($object_array = null)
	{
		if($object_array && !empty($object_array))
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
		$columns = self::get_columns();
		foreach($columns as $c)
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
