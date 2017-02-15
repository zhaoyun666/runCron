<?php
namespace Snails\library\dao\clw2;
use Snails\Core\DB;
use Snails\library\tools\snailRedis;

class MemberDao extends DB{
	private $table = 'car_member';
	private $db = 'clw';
	public function get()
	{
		$sql = sprintf('SELECT * FROM %s ORDER BY id DESC LIMIT 6', $this->table);
		return $this->initDb($this->db)->fetch($sql, self::FETCH_ASSOC);
		//snailRedis::getInstance('server1')->SunSet('A', 'xxxxxxxx------------aaaaaaaaaaaaaa');
		//return snailRedis::getInstance('server1')->SunGet('A');
	}
}