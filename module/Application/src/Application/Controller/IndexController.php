<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Lib\AppController;

class IndexController extends AppController {
  
  public function indexAction() {
  	$result = new ViewModel();
    return $result;
  }
}
