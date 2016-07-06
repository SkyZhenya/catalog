<?php
namespace Auth;

use Zend\ModuleManager\ModuleManager;
use Zend\EventManager\StaticEventManager;
use Zend\Mvc\ModuleRouteListener;
use Auth\View\Helper\HybridAuth as HybridAuthViewManager;

class Module 
{

    public function onBootstrap($e)
    {
    	$eventManager        = $e->getApplication()->getEventManager();
    	$moduleRouteListener = new ModuleRouteListener();
    	$moduleRouteListener->attach($eventManager);
    }

    public function getConfig()
    {	
    	$moduleConfig = include __DIR__ . '/config/module.config.php';
        $authModuleConfig = include __DIR__ . '/config/autoload/global.php';
        $config = array_merge($moduleConfig, $authModuleConfig);
        return $config;
    }

    public function getAutoloaderConfig()
    {

    	return array(
    			'Zend\Loader\StandardAutoloader' => array(
    					'namespaces' => array(
    							__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
    					),
    			),
    	);
    }

    public function init(\Zend\ModuleManager\ModuleManager $moduleManager)
    {
        $sharedEvents = $moduleManager->getEventManager()->getSharedManager();
        $sharedEvents->attach(__NAMESPACE__, 'dispatch', function($e) {
        }, 100);
    }
}
