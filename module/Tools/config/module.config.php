<?php

return [
	'console' => [
		'router' => [
			'routes' => [
				'cron' => [
					'options' => [
						'route' => 'cron <action>',
						'defaults' => [
							'controller' => 'Tools\Controller\Cron',
						],
					],
				],
				'devtools' => [
					'options' => [
						'route' => 'devtools <action> [<arg1>] [<arg2>]',
						'defaults' => [
							'controller' => 'Tools\Controller\Devtools',
						],
					],
				],
			],
		],
	],

	'controllers' => [
		'invokables' => [
			'Tools\Controller\Cron' => 'Tools\Controller\CronController',
			'Tools\Controller\Devtools' => 'Tools\Controller\DevtoolsController',
		],
	],
	'view_manager' => [
		'template_path_stack' => [
			__DIR__ . '/../view',
		],
	],
];
