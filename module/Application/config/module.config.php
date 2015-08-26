<?php

return array(
	'router' => array(
		'routes' => array(
			'home' => array(
				'type' => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
					'route'    => '/',
					'defaults' => array(
						'controller' => 'Application\Controller\Index',
						'action'     => 'index',
					),
				),
			),
			// The following is a route to simplify getting started creating
			// new controllers and actions without needing to create a new
			// module. Simply drop new controllers in, and you can access them
			// using the path /application/:controller/:action
			'application' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '[/:controller[/:action]][/]',
					'defaults' => array(
						'__NAMESPACE__' => 'Application\Controller',
						'controller' => 'Index',
						'action' => 'index',
					),
					'constraints' => array(
						'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
					),
				),
				'may_terminate' => true,
			),
		),
	),
	'service_manager' => [
		'factories' => [
			'Application\Lib\Redis' => function($sm) {
				return new Application\Lib\Redis();
			},
		],
	],
	'controllers' => array(
		'invokables' => array(
			'Application\Controller\Index' => 'Application\Controller\IndexController',
		),
	),
	'view_manager' => array(
		'display_not_found_reason' => DEBUG,
		'display_exceptions'       => DEBUG,
		'doctype'                  => 'HTML5',
		'not_found_template'       => 'error/404',
		'exception_template'       => 'error/index',
		'template_map' => array(
			'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
			'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
			'error/404'               => __DIR__ . '/../view/error/404.phtml',
			'error/index'             => __DIR__ . '/../view/error/index.phtml',
			'application/paginator' => __DIR__ . '/../view/service/pagination.phtml',
		),
		'template_path_stack' => array(
			__DIR__ . '/../view',
		),
	),
);
