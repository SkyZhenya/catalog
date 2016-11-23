<?php
use Zend\ServiceManager\ServiceManager;

return array(
	'router' => array(
		'routes' => array(
			'home' => array(
				'type' => 'literal',
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
			'cache' => function($serviceLocator) {
				$config = $serviceLocator->get('ApplicationConfig')['cache'];
				$redisWrapper = $serviceLocator->get('redis');
				$redis = new CodeIT\Cache\Redis($config, $redisWrapper);
				return $redis;
			},
			'redis' => function(ServiceManager $serviceManager) {
				$config = $serviceManager->get('ApplicationConfig')['redis'];
				return new \CodeIT\Cache\RedisWrapper(
					$config['host'],
					$config['port'],
					$config['db'],
					$config['options']
				);
			},
			'dbAdapter' => function(ServiceManager $serviceManager) {
				$config = $serviceManager->get('ApplicationConfig')['database'];
				$dbConfig = [
					'host' => $config['host'],
					'dbname' => $config['name'],
					'driver' => 'Pdo',
					'dsn' => 'mysql:dbname='.$config['name'].';host='.$config['host'],
					'username' => $config['user'],
					'password' => $config['password'],
					'driver_options' => [
						PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "UTF8", SQL_MODE="TRADITIONAL"',
						PDO::ATTR_EMULATE_PREPARES => false,
					],
				];
				if (defined('DEBUG_SQL') && DEBUG_SQL) {
					$adapter = new \BjyProfiler\Db\Adapter\ProfilingAdapter($dbConfig);
					$adapter->setProfiler(new \BjyProfiler\Db\Profiler\Profiler);
					$adapter->injectProfilingStatementPrototype();
					$serviceManager->setAlias('Zend\Db\Adapter\Adapter', 'dbAdapter');
				} else {
					$adapter = new Zend\Db\Adapter\Adapter($dbConfig);
				}
				return $adapter;
			},
		],
	],
	'controller_plugins' => [
		'invokables' => [
			'background' => 'Application\Lib\Controller\Plugin\Background',
		],
	],
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
	'view_helpers' => [
		'invokables' => [
			'captchainoutputimage' => 'Application\Lib\Form\View\Helper\Captcha\InOutputImage',
		],
	],
);
