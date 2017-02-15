<?php
$InitConfig = [
		'db' => [
				'default' => [
						'host' => '192.168.10.254',
						'user' => 'root',
						'password' => 'redhat',
						'dbName' => '17car',
						'port' => '3306',
						'charset' => 'utf8',	
				],
				'clw' => [
						'host' => '192.168.10.254',
						'user' => 'root',
						'password' => 'redhat',
						'dbName' => '17car_clw2',
						'port' => '3306',
						'charset' => 'utf8',
				],
		],
		'redis' => [
		    'server1' => [
		        'host' => '192.168.2.117',
		        'port' => '6379',
		    ]
		],
		'memcache' => [			
		],
		'errorPath' => ROOTDIR . 'app/cache/error/',
];