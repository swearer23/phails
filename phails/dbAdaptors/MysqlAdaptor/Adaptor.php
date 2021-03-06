<?php
/**
 * mysql引擎
 * @author gaofeng
 *
 */
require('ConnectionManager.php');

class Adaptor{

	public $use_db_config = null;
	public $table_name;
	public $conn;
	public $affected_rows = 0;
	public $model_class;

	public function __construct($model_name , $table_name , $db_name = null , $db_config = null)
	{
		$this->table_name = $table_name;
		$this->model_class = $model_name;
		$this->conn = ConnectionManager::getInstance($db_name , $this->use_db_config);
	}

	public function get_columns($tablename)
	{
		$q = $this->conn->prepare("DESCRIBE ".$tablename);
		$q->execute();
		$table_fields = $q->fetchAll(PDO::FETCH_COLUMN);
		return $table_fields;
	}
    public function update_records($query_object)
    {
        $sql = "UPDATE `".$this->table_name."` SET ";
        if(isset($query_object['update_records'])) {
            $sql .= $query_object['update_records'];
        }

        if(isset($query_object['condition']->condition_statement)) {
            $sql .= $query_object["condition"]->condition_statement;
        }
        $result = $this->exec($sql);
        return $result['ret'];
    }
    public function drop_records($query_object)
    {
        $sql = "DELETE FROM `" . $this->table_name . "`";
        if(isset($query_object["condition"]->condition_statement))
        {
            $sql .= $query_object["condition"]->condition_statement;
        }
        $result = $this->exec($sql);

        return $result['ret'];
    }
/**
* 直接执行一条dml语句
*/
    public function execute($sql, $array = null) {
        try {
            $this->conn->beginTransaction ();
            $stmt = $this->conn->prepare ( $sql );
            $stmt->execute ( $array );
            $this->conn->commit ();
            return true;
        } catch ( PDOexecption $e ) {
            $this->conn->rollBack ();
            return false;
        }
    }
    /**
     * 直接执行一条dql语句
     */
    public function query($sql, $array = null) {
        $stmt = $this->conn->prepare ( $sql );
        $stmt->execute ( $array );
        return $stmt->fetchAll ( PDO::FETCH_CLASS, $this->model_class );
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
		$values = array();
		foreach(array_values($orm_object) as $value){
			if(trim($value) == ""){
				array_push($values ,  "NULL");
			}else{
				array_push($values , "'".$value."'");
			}
		}
		$sql = "insert into `".$this->table_name."`(".$column_str.") values(".implode("," , $values).")";
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
		if(isset($query_object["order"])){
			$sql .= " ORDER BY " . $query_object["order"];
		}
		if(isset($query_object["limit"])){
			$sql .= " LIMIT " . $query_object["limit"];
		}
		if (isset($query_object['offset'])) {
			$sql .= " OFFSET " . $query_object["offset"];
		}

		$result = $this->exec($sql);
		if($cols == "*"){
			$res = $result["statement"]->fetchAll(PDO::FETCH_CLASS , $this->model_class);
		}else{
			$res = $result["statement"]->fetchAll(PDO::FETCH_COLUMN);
		}
        return $res;
	}

	public function findOne($query_object)
	{
		$result = $this->find($query_object);
		if($result){
			return $result[0];
		}else{
			return null;
		}
	}

	public function count($condition = NULL)
	{
		$sql = "SELECT COUNT(*) FROM ".$this->table_name;
		$sql .= $condition ? $condition->condition_statement : '';
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
            if(trim($v) == ""){
			    array_push($updates ,"`" . $k . "` = NULL");
            }else{
			    array_push($updates , "`" . $k . "` = '" . $orm_object[$k] . "'");
            }
		}
		$updates = implode(" , " , $updates);
		$sql = "UPDATE ".$this->table_name." SET ".$updates." WHERE id='".$orm_object["id"]."'";
		$result = $this->exec($sql);
		return $result["ret"];
	}

	public function destroy($orm_object)
	{
		$id = isset($orm_object["id"]) ? $orm_object["id"] : null;
		if($id)
		{
			$sql = "DELETE FROM ".$this->table_name;
			$sql .= " WHERE id = " . $id;
			$result = $this->exec($sql);
			return $result;
		}else{
			return false;
		}
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
