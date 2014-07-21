<?php
namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\EventManager\StaticEventManager;

class Module implements AutoloaderProviderInterface {
	public function onBootstrap(MvcEvent $e) {
		$eventManager        = $e->getApplication()->getEventManager();
    $moduleRouteListener = new ModuleRouteListener();
    $moduleRouteListener->attach($eventManager);
        
		$app = $e->getApplication();
		$serviceManager = $app->getServiceManager();
		$serviceManager->get('viewhelpermanager')->setFactory('myviewalias', function($sm) use ($e) {
			return new \Application\View\AppViewHelper($e->getRouteMatch());
		});
  }

	public function getConfig()	{
		return include __DIR__ . '/config/module.config.php';
	}

	public function getAutoloaderConfig() {
		return array(
			'Zend\Loader\StandardAutoloader' => array(
				'namespaces' => array(
					__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
				),
			),
		);
	}
	public function getViewHelperConfig() {
		return array(
			'factories' => array(

				'getLang' => function ($sm) {
					$viewHelper = new \Application\View\Helper\Lang;
					return $viewHelper;
				},
        'wrappedElement' => function ($sm) {
            $viewHelper = new \Application\View\WrappedElement;
            return $viewHelper;
        },
        'wrappedForm' => function ($sm) {
            $viewHelper = new \Application\View\WrappedForm;
            return $viewHelper;
        },
        'getUser' => function ($sm) {
					$viewHelper = new \Application\View\Helper\User;
					return $viewHelper;
				},
			)
		);
	}

}
