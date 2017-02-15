<?php
namespace library\dao\clw2;
use Core\DB;

class MemberDao extends DB{
	private $table = 'car_member';
	private $db = 'clw';
	public function get()
	{
		$sql = sprintf('SELECT * FROM %s ORDER BY id DESC LIMIT 6', $this->table);
		return $this->initDb($this->db)->fetch($sql, self::FETCH_ASSOC);
	}
}