<?php
namespace Application\Controller;

use Zend\View\Model\ViewModel;
use CodeIT\Controller\AbstractController;

class Index extends AbstractController {

	public function indexAction() {
		$result = new ViewModel();
		return $result;
	}
}
