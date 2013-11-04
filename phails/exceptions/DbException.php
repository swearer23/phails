<?php
class DbException extends BaseException{
	
	/**
	 * 异常码映射关系
	 * @var unknown_type
	 */
	public static $code_mapping = array(
			'PDOException'=>array(
					'2006'=>DbException::CONN_LOST,
					'1146'=>DbException::TABLE_NOT_EXIST,
					'23000'=>DbException::OPT_FAILURE,
			),
	);
	
	//连接异常错误码定义
	const CONN_LOST = 90001;
	//库异常错误码定义
	const DB_NOT_EXIST = 90101;
	//表异常错误码定义
	const TABLE_NOT_EXIST = 90201;
	//参数错误
	const PARAMS_ERROR = 90301;
	//操作错误
	const OPT_FAILURE = 90401;
	//事务失败
	const TRANSACTION_FAIL = 90402;
}
