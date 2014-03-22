<?php
/**
 * mysql引擎
 * @author gaofeng
 *
 */
require('ConnectionManager.php');

class Adaptor{

	private $use_db_config = null;
	private $table_name;
	private $conn;
	private $affected_rows = 0;
	private $model_name;

	public function __construct($model_name , $table_name , $db_name = null , $db_config = null)
	{
		$this->table_name = $table_name;
		$this->model_name = $model_name;
		$this->conn = ConnectionManager::getInstance($db_name , $this->use_db_config);
	}

	public function get_columns($tablename)
	{
		$q = $this->conn->prepare("DESCRIBE ".$tablename);
		$q->execute();
		$table_fields = $q->fetchAll(PDO::FETCH_COLUMN);
		return $table_fields;
	}

	public function create($orm_object)
	{
		foreach($orm_object as $k=>$v)
		{
			$orm_object[$k]=addslashes ($v);	
		}
		$column_str = implode("`,`",array_keys($orm_object));
		if(!empty($column_str)){
			$column_str = "`".$column_str."`";
		}
		$values = implode("','",array_values($orm_object));
		if(!empty($values)){
			$values = "'".$values."'";
		}
		$sql = "insert into `".$this->table_name."`(".$column_str.") values(".$values.")";
		$result = $this->exec($sql);
		return $this->conn->lastInsertId();
	}

	public function find($query_object)
	{
		$cols = isset($query_object["cols"]) ? $query_object["cols"] : "*";
		$sql = "SELECT ".$cols." FROM ".$this->table_name;
		if(isset($query_object["condition"]->condition_statement))
		{
			$sql .= $query_object["condition"]->condition_statement;
		}
		if(isset($query_object["limit"])){
			$sql .= " LIMIT " . $query_object["limit"];
		}
		$result = $this->exec($sql);
		if($cols == "*"){
			return $result["statement"]->fetchAll(PDO::FETCH_CLASS , $this->model_name);
		}else{
			return $result["statement"]->fetchAll(PDO::FETCH_COLUMN, $this->model_name);
		}
	}

	public function count($query_object)
	{
		$sql = "SELECT COUNT(*) FROM ".$this->table_name;
		if(isset($query_object["condition"]))
		{
			$sql .= $query_object["condition"]->condition_statement;
		}
		$result = $this->exec($sql);
		return intval($result["statement"]->fetchColumn());
	}

	public function update($orm_object)
	{
		if(!isset($orm_object["id"]))
		{
			throw new DbException("id missing in updating model " . $this->table_name , DbException::PARAMS_ERROR);
		}
		$updates = array();
		foreach($orm_object as $k => $v)
		{
			$orm_object[$k] = addslashes($v);
			array_push($updates , $k . "='" . $v . "'");
		}
		$updates = implode(" , " , $updates);
		$sql = "UPDATE ".$this->table_name." SET ".$updates." WHERE id='".$orm_object["id"]."'";
		$result = $this->exec($sql);
		return $result["ret"];
	}

	private function exec($sql)
	{
		$sql .= ";";
		$statement = $this->conn->prepare($sql);
		$res = array(
			"ret" 		=> $statement->execute(),
			"statement"	=> $statement
		);
		return $res;
	}

	private function parse_condition($condition_object)
	{
		$condition_template = array(
			"cols" 		=> "*",
			"condition" => "",
			"order"		=> "",
			"limit"		=> ""
		);
	}
	
	/*
	public function getQueryStatement($columns="*",$table_name,$wheres,$order,$limit,$other){
		if(empty($columns)||empty($table_name)||empty($wheres)){
			throw new DbException("query params ".$columns.",".$table_name." is null", DbException::PARAMS_ERROR);
		}
		$sql = "SELECT ".$columns." FROM ".$table_name." WHERE " . $this->doWhere($wheres);
		if(!empty($other)){
			$sql .= " ".$other." ";
		}
		if(!empty($order)){
			$sql .= " ORDER BY ".$order." ";
		}
		if(!empty($limit)){
			$sql .= " LIMIT ".$limit." ";
        }
		return $sql;
	}

	public function getDeleteStatement($table_name,$wheres){
		if(empty($table_name)||empty($wheres)){
			throw new DbException("delete params ".$table_name.",".$wheres." is null", DbException::PARAMS_ERROR);
		}
		$sql = "DELETE FROM `{$table_name}` WHERE ".$this->doWhere($wheres);
		return $sql;
	}

	private function doWhere($wheres){
		if(!is_null($wheres)&&!is_array($wheres)){
			throw new DbException('error where:'.json_encode($wheres), DbException::PARAMS_ERROR);
		}
		$where = " 1 ";
		foreach($wheres as $k=>$v){
			if(empty($v) && !is_int($v)){
				continue;
			}
			if(is_array($v)){
				$where .= " AND `".$k."` in( '".implode ('\',\'' ,$v)."' )";
			}else{
				$where .= " AND `".$k."` = '".addslashes($v)."' ";
			}
		}
		return $where;
		if(trim($where)=='1'){
			throw new DbException('error where:'.$where, DbException::PARAMS_ERROR);
        }
		return $where;
	}

	public function delete($sql){
		$re = $this->exec($sql);
		if($re === false){
			$this->affected_rows = 0;
			return false;
		}else{
			$this->affected_rows = $re;
			return true;
		}
	}

	public function insert($sql){
		$re = $this->exec($sql);
		if($re === false){
			$this->affected_rows = 0;
			return false;
		}else{
			$this->affected_rows = $re;
			return true;
		}
	}

	public function getInsertId(){
		return $this->lastInsertId();
	}

	public function fetchArray($rs){
		return $rs->fetch(PDO::FETCH_ASSOC);
	}
	
	public function getAffectedRows(){
		return $this->affected_rows;
	}

	*/

}
