<?php
namespace Application;

use CodeIT\Utils\Registry;
use Zend\Http\Header\ContentEncoding;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;

class Module implements AutoloaderProviderInterface {

	public function onBootstrap(MvcEvent $e) {
		$eventManager        = $e->getApplication()->getEventManager();
		$moduleRouteListener = new ModuleRouteListener();
		$moduleRouteListener->attach($eventManager);

		$app = $e->getApplication();
		$serviceManager = $app->getServiceManager();
		$serviceManager->get('viewhelpermanager')->setFactory('myviewalias', function($sm) use ($e) {
			return new \CodeIT\View\Helper\AppViewHelper($e->getRouteMatch());
		});

		$sharedManager = $app->getEventManager()->getSharedManager();
		$sharedManager->attach('Zend\Mvc\Application', 'dispatch.error',
			function($e) use ($serviceManager) {
				if ($exception = $e->getParam('exception')) {
					$error = 'Error 500 (Code '.$exception->getCode().'): '.$exception->getMessage().
						"\nURI is ".$e->getRequest()->getRequestUri()."\n".$exception->getTraceAsString();
					$error = explode("\n", $error);
					foreach($error as $line) {
						error_log($line);
					}
				}
			}
		);

		Registry::set('sm', $serviceManager);

		if(defined('GZIP_OUTPUT') && GZIP_OUTPUT) {
			$eventManager->attach('finish', array($this, 'compressOutput'), -100000);
		}
	}

	public function getConfig() {
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
	
	public function compressOutput($e) {
		$response = $e->getResponse();

		if(is_a($response, '\Zend\Console\Response'))return;
		if(is_a($response, '\Zend\Http\Response\Stream'))return;
		
		if (get_class($response) !== 'Zend\Console\Response') {
			$content = $response->getBody();
			$content = str_replace('  ', ' ', str_replace("\r", ' ', str_replace("\t", ' ', $content)));

			if($e->getRequest()->getHeader('Accept-Encoding') && $e->getRequest()->getHeader('Accept-Encoding')->hasEncoding('gzip')) {
				$response->getHeaders()->addHeader(new ContentEncoding('gzip'));
				$content = gzencode($content, 9);
			}

			$response->setContent($content);
		}
	}

	public function getViewHelperConfig() {
		return array(
			'factories' => [
				'getLang' => function ($sm) {
					$viewHelper = new \CodeIT\View\Helper\Lang;
					return $viewHelper;
				},
				'getUser' => function ($sm) {
					return new \CodeIT\View\Helper\User;
				},
				'wrappedElement' => function ($sm) {
					$viewHelper = new \CodeIT\View\Helper\WrappedElement;
					return $viewHelper;
				},
				'wrappedForm' => function ($sm) {
					$viewHelper = new \CodeIT\View\Helper\WrappedForm;
					return $viewHelper;
				},
			],
		);
	}

}
