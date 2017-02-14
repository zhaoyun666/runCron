<?php
namespace Core;
class Snail{
	
	/**
	 * @params $argv = [
	 * 	1 => 模块
	 * 	2 => controller
	 * 	3 => action
	 * ]
	 * @author zhaoce@linewin.cc
	 * @date 2017年2月14日   下午2:40:32
	 * @return:
	 */
	public static function run($argv)
	{
		if(count($argv) < 3) return;
		$className = '\\' . APPNAME . '\\' . MODULES . '\\' . $argv[1] . '\\' . $argv[2] . MODULE_EXT;
		$action = $argv[3];
		$initClass = new $className();
		$initClass->$action();
	}
}