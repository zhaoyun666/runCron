<?php

/**
 * Mysql数据库操作类 v2.0
 * 2015.5.6 by Aboc QQ:9986584
 * 增加文件缓存
 *
 */
namespace Snails\Core;

class DB {
	/*
	 * 编码
	 */
	private $_charset = 'utf8';
	
	/*
	 * 最后一次插入的ID
	 */
	private $_lastId = 0;
	
	/**
	 * sql语句
	 *
	 * @var unknown_type
	 */
	private $_sql = '';
	
	const FETCH_OBJ = 1;

	const FETCH_ASSOC = 2;

	private static $con = null;

	private static $database = null;

	public static function initDb($db = "")
	{
		self::connection($db);
		if (self::$database === null) {
			self::$database = new DB();
		}
		return self::$database;
	}
	public function connection($db)
	{
		$config = C::getConfig(DB_KEY . '.' . $db);
		if (! self::$con) {
			self::$con = new \mysqli($config['host'], $config['user'], $config['password'], $config['dbName'], $config['port']);
		}

		self::$con->set_charset($config['charset'] ?? $this->_charset);
		if (self::$con->connect_error) {
			die("Connect error" .\mysqli_connect_error() . PHP_EOL);
		}
	}
	public function execute($sql)
	{
		$this->query($sql);
		$insertId = self::insertID();
		return $insertId;
	}
	private function query($sql)
	{
		$this->_sql = $sql;
		$query = \mysqli_query(self::$con, $sql);
		if(!$query){
			ob_start();
			echo 'Mysql Error Start Date：' . date("Y-m-d H:i:s") . "\r\n";
			echo '错误号：' . \mysqli_errno(self::$con) . "\r\n";
			echo '最后的错误信息：' . \mysqli_error(self::$con) . "\r\n";
			echo '最后mysql执行中的错误列表' . json_encode(\mysqli_error_list(self::$con)) . "\r\n";
			echo "Mysql Error End ----------------------------------------------\r\n";
			$error_log = ob_get_contents();
			ob_end_clean();
			self::_halt($error_log);
			die();
		}
		\mysqli_affected_rows(self::$con);
		return $query;
	}
	/**
	 * 数据更新
	 * @date: 2016-8-21 下午6:01:36
	 * @author: zhaoce@linewin.cc
	 */
	public function updateByField($table, $data, $where)
	{
		if(empty($data) || empty($table)) return false;
		$set = '';
		foreach($data as $key=>$val){
			$set .= $key . "='" . $val ."',";
		}
		if($set) $set = substr($set, 0, strlen($set) - 1);
		if($where){
			if(is_array($where)){
				foreach($where as $k=>$val){
					$tmp[] = $k . "='" . $val ."'";
				}
				$where = implode(' AND ', $tmp);
				if($where) $where = 'WHERE ' . $where;
			}else{
				$where = ' WHERE ' . $where;
			}
		}
		$update = sprintf('UPDATE %s SET %s %s', $table, $set, $where);
		 
		return $this->query($update);
	}
	/**
	 * 数据插入
	 * @date: 2016-8-21 下午6:21:36
	 * @author: zhaoce@linewin.cc
	 */
	public function insert($table, $data): int
	{
		if(empty($data) || empty($table)) return false;
		$field = join(',', array_keys($data));
		$value = '';
		foreach($data as $key => $val){
			$value .= "'" . $val ."',";
		}
		if($value) $value = substr($value, 0, strlen($value) - 1);
		$insert = sprintf('INSERT INTO %s(%s) values(%s)', $table, $field, $value);
		$this->query($insert);
		$insertId = self::insertID();
		self::close();
		return $insertId;
	}
	/**
	 * @date: 2016-4-21 上午10:16:10
	 * @author : zhaoce@linewin.cc
	 * @return : 对某张表进行批量插入数据
	 */
	public function multiExecute($table, $data)
	{
		// 将insert数据进行组装 批量进入插入操作
		if (!\is_array($data) || empty($data))
			return false;
			$keys = \array_keys($data[0]);
			$join = "";
			$k = 0;
			$sql = sprintf("insert into %s values", join(",", $keys));
			foreach ($data as $k => $v) {
				if ($k < 100) {
					$join .= "('" . join("', '", $data[0]) . "'),";
					$sql .= $join;
				} else {
					$join .= "('" . join("', '", $data[0]) . "');";
					$sql .= $join;
					$res = self::execute($sql);
					$sql = sprintf("insert into %s values", join(",", $keys));
				}
				$k ++;
			}
			// 生下来一部分sql也要进行执行
			if (substr($sql, 0, - 1) == ",") {
				$new_sql = substr($sql, 0, (strlen($sql) - 1));
				$res = self::execute($new_sql);
			}
			self::close();
			return $res;
	}
	/*
	 * 获取多条数据
	 */
	public function fetch($sql, $dataType): array
	{
		$res = self::query($sql);
		$result = [];
		$data = [];
		while ($row = $this->dataType($dataType, $res)) {
			$result[] = $row;
		}
		mysqli_free_result($res);
		self::close();
		return $result;
	}
	/*
	 * 获取一条数据
	 */
	public function fetchOne($sql, $dateType)
	{
		if(!strpos($sql, "limit") && !strpos($sql, "LIMIT")){
			$sql = $sql . " LIMIT 1";
		}
		$res = self::query($sql);
		$result = [];
		while($row=$this->dataType($dataType, $res)){
			$result = $row;
		}
		mysqli_free_result($res);
		self::close();
		return $result;
	}
	public function dataType($dataType, $result)
	{
		switch ($dataType) {
			case 1:
				$obj = \mysqli_fetch_object($result);
				break;
			case 2:
				$obj = \mysqli_fetch_assoc($result);
				break;
			default:
				$obj = \mysqli_fetch_array($result);
				break;
		}
		return $obj;
	}
	/**
	 * truncate table
	 * @param string $table
	 */
	public function truncate($table)
	{
		\mysqli_query(self::$con, "TRUNCATE TABLE $table");
		\mysqli_close(self::$con);
		return \mysqli_error(self::$con);
	}
	/**
	 * Insert ID
	 * @return int
	 */
	public function insertID()
	{
		return \mysqli_insert_id(self::$con);
	}
	/**
	 * 获取最后一次执行的sql语句
	 */
	private function getLastSql() {
		return $this->_sql;
	}
	/**
	 * 获取最后一次影响的记录数
	 * @return type
	 */
	public function fetchChangeRow() {
		return \mysqli_affected_rows ();
	}
	/**
	 * 释放查询结果
	 */
	private function free() {
		\mysqli_free_result ( $this->con );
	}
	/**
	 * 关闭连接
	 */
	public function close()
	{
		if (self::$con) {
			\mysqli_close(self::$con);
		}
		self::$con = null;
	}
	/*
	 * mysql 错误日志
	 */
	private function _halt($log)
	{
		global $InitConfig;
		$filename = $InitConfig['errorPath'] . date ( "Y-m-d" ) . '.log';
		if (! $fp = fopen ( $filename, 'a+' )) {
			exit('错误日志打开失败,');
		}
		if (fwrite ( $fp, $log ) === FALSE) {
			exit('错误日志写入失败');
		}
		fclose ( $fp );
	}
	/**
	 * 数据数据库所用大小
	 * @param unknown_type $dbname
	 * @return unknown
	 */
	public function getSqlSize($dbname) {
		$sql = "SHOW TABLE STATUS from $dbname";
		$rows = $this->fetch($sql, self::FETCH_ASSOC);
		$total = 0;
		foreach ( $rows as $row ) {
			$total += $row ['Data_length'];
			$total += $row ['Index_length'];
		}
		return round ( $total / (1024 * 1024), 2 );
	}

	/**
	 * 将变量的单引号或双引号转义
	 * @param unknown_type $string
	 */
	private function strtag($string1) {
		if (is_array ( $string1 )) {
			foreach ( $string1 as $key => $value ) {
				$stringnew [$this->strtag ( $key )] = $this->strtag ( $value );
			}
		} else {
			// 在此做转义,对单引号
			$stringnew = \mysqli_real_escape_string($this->con, $string1);
		}
		return $stringnew;
	}
	/**
	 * 将数组转化为SQL接受的条件样式
	 * @param unknown_type $array
	 */
	public function chageArray($array) {
		// MYSQL支持insert into joincart set session_id = 'dddd',product_id='44',number='7',jointime='456465'
		// 所以更新和插入可以使用同一组数据
		$array = $this->strtag ( $array ); // 转义
		$str = '';
		foreach ( $array as $key => $value ) {
			$str .= empty ( $str ) ? "`" . $key . "`='" . $value . "'" : ", `" . $key . "`='" . $value . "'";
		}
		return $str;
	}
	/**
	 * 获取最后一次影响的Id
	 */
	public function lastId() {
		$this->_lastId = \mysqli_insert_id ( self::$con );
		return $this->_lastId;
	}
	/**
	 * 获取符合条件的记录数
	 */
	public function fetchNum($sql) {
		$result = $this->query($sql);
		$num = \mysqli_num_rows($result);
		return $num;
	}
	/**
	 * 替换并插入
	 *
	 * @param unknown_type $table
	 * @param unknown_type $array
	 */
	public function replaceInsert($table, $array) {
		if (!is_array($array)) return false;
		$array = $this->strtag($array); // 转义
		$str = '';
		$val = '';
		foreach ( $array as $key => $value ) {
			$str .= ($str != '') ? ",`$key`" : "`$key`";
			$val .= ($val != '') ? ",'$value'" : "'$value'";
		}
		$sql = 'replace into `' . $table . '` (' . $str . ') values(' . $val . ')';
		if ($this->query ( $sql )) {
			$this->lastId ();
			return $this->_lastId ? $this->_lastId : true;
		} else {
			return false;
		}
	}
}
?>