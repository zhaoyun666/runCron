<?php
namespace app\modules\member;
use Snails\library\dao\clw2\MemberDao;

class carMileModule{
	
	public function index()
	{
		$member = new MemberDao();
		var_dump($member->get());
	}
}