<?php
namespace Admin\View;
use Zend\View\Helper\AbstractHelper;

class AppViewHelper extends AbstractHelper {
	protected $route;

	public function __construct($route) {
		if(!$route) exit();
		$this->route = $route;
	}

	public function prepareControllerAction() {
		$controller = $this->route->getParam('controller', 'index');
		$action = $this->route->getParam('action', 'index');
		$lang = $this->route->getParam('lang', 'en');
		$namespace = $this->route->getParam('__NAMESPACE__', '');
		preg_match('#(\w+)$#', $controller, $mC);
		preg_match('#(\w+)$#', $action, $mA);
		$this->view->controller = $mC[1];
		$this->view->action = $mA[1];
		$this->view->current_lang = $lang;
		
	}
	
}
