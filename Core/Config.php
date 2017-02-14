<?php
namespace Core;
class Config{
	
	public static function getConfig($path): array{
		global $InitConfig;
		if (empty($path)) return [];
		$tmp = $InitConfig;
		$paths = explode('.', $path);
		foreach ($paths as $item) {
			$tmp = $tmp[$item];
		}
		return $tmp;
	}
}