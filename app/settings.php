<?php

$cacheDir = __DIR__ . '/../cache';

return (require __DIR__ . '/settings.local.php') + [
	'settings' => [
		'tracy' => [
			'logDir' => __DIR__ . '/../logs',
			'mode' => \Tracy\Debugger::DETECT,
			'showPhpInfoPanel' => 1,
			'showDoctrinePanel' => 'em',
			'configs' => [
				'XDebugHelperIDEKey' => 'PHPSTORM',
				'ConsoleNoLogin' => 0,
				'ConsoleAccounts' => [],
				'ConsoleHashAlgorithm' => 'sha1',
				'ConsoleHomeDirectory' => DIR,
				'ConsoleTerminalJs' => '/assets/js/jquery.terminal.min.js',
				'ConsoleTerminalCss' => '/assets/css/jquery.terminal.min.css',
				'ProfilerPanel' => [
					'show' => [
						'memoryUsageChart' => 1, // or false
						'shortProfiles' => true, // or false
						'timeLines' => true // or false
					]
				]
			]
		],
		'addContentLengthHeader' => false,
		'routerCacheFile' => $cacheDir . '/router.data',
		'doctrine' => [
			'meta' => [
				'entity_path' => [
					'app/Entity'
				],
				'auto_generate_proxies' => false,
				'proxy_dir' =>  $cacheDir . '/proxies',
				'cache' => new \Doctrine\Common\Cache\FilesystemCache($cacheDir),
			],
			'connection' => [
				'driver'   => 'pdo_mysql',
				'host'     => '127.0.0.1',
				'dbname'   => '',
				'user'     => '',
				'password' => '',
				'driverOptions' => [
					PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "utf8"',
				]
			]
		]
	],
];
