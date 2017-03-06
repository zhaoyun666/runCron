<?php
namespace Snails\Core;
class C{
	
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