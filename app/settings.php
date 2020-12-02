<?php

$cacheDir = __DIR__ . '/../cache';

return array_replace_recursive([
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
				'ConsoleHomeDirectory' => __DIR__,
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
		'outputBuffering' => false,
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
		],
		'oauth' => [
			'publicKey' => null,
			'privateKey' => null,
			'encryptionKey' => null,
		],
        'mailer' => [
            'dsn' => '',
            'salt' => '',
            'client_srv_redirect' => '',
        ],
        'socketSettings' => [
            'route' => [
                'host' => null,
                'port' => null,
                'path' => null,
            ],
            'auth' => [
                'publicKeyToWSServer' => null,
                'psw' => null
            ]
        ]
	],
], require __DIR__ . '/settings.local.php');
