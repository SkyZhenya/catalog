<?php
namespace Application\Controller;

use Zend\View\Model\ViewModel;
use Application\Lib\AppController;

class IndexController extends AppController {

	public function indexAction() {
		$result = new ViewModel();
		return $result;
	}
}
