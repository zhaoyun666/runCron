<?php

/**
 * Mysql数据库操作类 v2.0
 * 2015.5.6 by Aboc QQ:9986584
 * 增加文件缓存
 *
 */
namespace Core;

class DB {
	/*
	 * 编码
	 */
	private $_charset = 'utf8';
	
	// 数据库配置
	private $_config = [ ];
	
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
	private $db = '';
	// 数据库连接句柄
	public $con = null;
	public $database = null;
	
	// 初始化数据库
	public function initDb($db = "") {
		$this->db = $db;
		if ($this->database == null) {
			$this->database = new DB ();
		}
		return $this->database;
	}
	public function __construct() {
		$config = Config::getConfig(DB_KEY . '.' . $this->db);
		self::checkConfig($config);
		if ($this->con == null) {
			$this->con = $this->connect ();
		}
		\mysqli_set_charset($this->con, $this->_config['charset'] ?? $this->_charset);
	}
	/**
	 * 判断config变量
	 *
	 * @param unknown_type $config        	
	 */
	private function checkConfig($config) {
		foreach ( $config as $key => $value ) {
			$this->_config [$key] = empty ( $value ) ? $this->_config [$key] : $value;
		}
	}
	/*
	 * 连接数据库
	 */
	private function connect() {
		return \mysqli_connect( $this->_config ['host'], $this->_config ['user'], $this->_config ['password'], $this->_config['dbName'], $this->_config['port'] ) or die ( '数据库连接失败' . \mysqli_errno ($this->con) );
	}
	/**
	 * 将变量的单引号或双引号转义
	 *
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
	 *
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
	 * 执行查询语句
	 *
	 * @return bool
	 */
	public function query($sql) {
		$this->_sql = $sql;
		if (! $result = \mysqli_query($this->con, $this->_sql)) {
			print_r(array(
					'subject' => date ( "Y-m-d H:i:s" ) . \mysqli_error($this->con),
					'msg' => \mysqli_error($this->con),
					'data' => \mysqli_error_list($this->con)
			));exit();
		} else {
			return $result;
		}
	}
	/**
	 * 插入记录
	 */
	public function insert($table, $array) {
		if (! is_array ( $array ))
			return false;
		$array = $this->strtag ( $array ); // 转义
		$str = '';
		$val = '';
		foreach ( $array as $key => $value ) {
			$str .= ($str != '') ? ",`$key`" : "`$key`";
			$val .= ($val != '') ? ",'$value'" : "'$value'";
		}
		$sql = 'insert into `' . $table . '` (' . $str . ') values(' . $val . ')';
		if ($this->query ( $sql )) {
			$this->lastId ();
			return $this->_lastId ? $this->_lastId : true;
		} else {
			return false;
		}
	}
	/**
	 * 替换并插入
	 *
	 * @param unknown_type $table        	
	 * @param unknown_type $array        	
	 */
	public function replaceInsert($table, $array) {
		if (! is_array ( $array ))
			return false;
		$array = $this->strtag ( $array ); // 转义
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
	/**
	 * 批量插入记录
	 *
	 * @param $table 表名        	
	 * @param $batchArray 批量数据
	 *        	,二维数组,健名必需相同,否则不能插入
	 */
	public function insertBatch($table, $batchArray) {
		if (! is_array ( $batchArray ))
			return false;
		$str = '';
		$val = '';
		$vals = array ();
		foreach ( $batchArray as $keys => $row ) {
			if (! is_array ( $row ))
				return false;
			foreach ( $row as $key => $value ) {
				if ($keys == 0)
					$str .= ($str != '') ? ",`$key`" : "`$key`";
				$val .= ($val != '') ? ",'$value'" : "'$value'";
			}
			$vals [$keys] = '(' . $val . ')';
			$val = '';
		}
		$vals = implode ( ',', $vals );
		$sql = 'insert into `' . $table . '` (' . $str . ') values ' . $vals;
		if ($this->query ( $sql )) {
			$this->lastId ();
			return $this->_lastId ? $this->_lastId : true;
		} else {
			return false;
		}
	}
	/**
	 * 更新记录
	 */
	public function update($table, $array, $where = NULL) {
		if ($where == NULL) {
			$sql = 'update `' . $table . '` set ' . $this->chageArray ( $array );
		} else {
			$sql = 'update `' . $table . '` set ' . $this->chageArray ( $array ) . ' where ' . $where;
		}
		if ($res = $this->query ( $sql )) {
			return $res;
		} else {
			return false;
		}
	}
	/**
	 * 删除记录
	 */
	public function delete($table, $where = NULL) {
		if ($where == NULL) {
			$sql = 'delete from `' . $table . '`';
		} else {
			$sql = 'delete from `' . $table . '` where ' . $where;
		}
		if ($this->query ( $sql )) {
			return true;
		} else {
			return false;
		}
	}
	/**
	 * 获取一条记录
	 */
	public function fetchRow($sql, $cacheTime = 0) {
		$reult = $this->query ( $sql );
		$row = \mysqli_fetch_assoc ( $reult );
		if (! empty ( $row )) {
			foreach ( $row as $key => $value ) {
				$row [$key] = \stripslashes ( $value );
			}
		}
		return $row;
	}
	/**
	 * 获取所有记录/用的mysql_fetch_assoc循环
	 */
	public function fetchAll($sql) {
		$result = $this->query ( $sql );
		if ($result !== false) {
			$arr = array ();
			while ( $row = \mysqli_fetch_assoc ( $result ) ) {
				if (! empty ( $row )) {
					foreach ( $row as $key => $value ) {
						$row [$key] = \stripslashes ( $value );
					}
				}
				$arr [] = $row;
			}
		} else {
			return array ();
		}
	}
	/**
	 * 获取最后一次影响的Id
	 */
	public function lastId() {
		$this->_lastId = \mysqli_insert_id ( $this->_Db );
		return $this->_lastId;
	}
	/**
	 * 获取符合条件的记录数
	 */
	public function fetchNum($sql) {
		$reult = $this->query ( $sql );
		$num = \mysqli_num_rows ( $reult );
		return $num;
	}
	/**
	 * 输出适合的where语句
	 */
	private function quoteInto($string, $value) {
		$value = $this->strtag ( $value );
		if (\is_numeric ( $value )) {
			$string = \str_replace ( '?', $value, $string );
		} else {
			$string = \str_replace ( '?', "'" . $value . "'", $string );
		}
		return $string;
	}
	/**
	 * 数据数据库所用大小
	 *
	 * @param unknown_type $dbname        	
	 * @return unknown
	 */
	public function getSqlSize($dbname) {
		$sql = "SHOW TABLE STATUS from $dbname";
		$rows = $this->fetchAll ( $sql );
		$total = 0;
		foreach ( $rows as $row ) {
			$total += $row ['Data_length'];
			$total += $row ['Index_length'];
		}
		return round ( $total / (1024 * 1024), 2 );
	}
	/**
	 * 写错误日志
	 *
	 * @param unknown_type $log        	
	 */
	private function createErrorLog($sql) {
		$log = array (
				date ( "Y-m-d H:i:s" ),
				$sql,
				mysql_error () 
		);
		$log = implode ( ' - ', $log ) . "\r\n";
		global $InitConfig;
		$filename = $InitConfig['errorPath'] . date ( "Y-m" ) . '.txt';
		if (! $fp = fopen ( $filename, 'a+' )) {
			echo '错误日志打开失败,';
		}
		if (fwrite ( $fp, $log ) === FALSE) {
			echo '错误日志写入失败';
		}
		fclose ( $fp );
	}
	/**
	 * 获取最后一次执行的sql语句
	 */
	private function getLastSql() {
		return $this->_sql;
	}
	/**
	 * 获取最后一次影响的记录数
	 *
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
}
?>