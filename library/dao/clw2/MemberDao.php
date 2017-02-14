<?php
namespace library\dao\clw2;
use Core\DB;

class MemberDao extends DB{
	private $table = 'car_member';
	private $db = 'clw';
	public function get()
	{
		$sql = sprintf('SELECT * FROM %s', $this->table);
		$this->initDb($this->db);
		return $this->database;
	}
}