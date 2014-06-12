<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Lib\AppController;

class UserController extends AppController {
    
	public function loginAction() {
		if($this->user->getId()) {
			return $this->redirect()->toRoute('home');
		}

		$err = isset($_GET['err']) ? htmlspecialchars($_GET['err']) : '';
		$form = new \Application\Form\LoginForm();
		$form->setInputFilter($form->getLoginInputFilter());
		$data = $this->request->getPost();
		
		$retUrl = $this->request->getQuery('r');
		if(!empty($data->retUrl)) {
			$retUrl = $data->retUrl;
		}
		
		$form->setData($data);

		if($this->request->isPost()){
			if ($form->isValid()) {
				$data = $form->getData();

				$user = $this->user->checkLogin($data['email'], $data['password']);
				if($user) {
					$_SESSION['id'] = $user->id;
					
					if(!empty($retUrl)){
						$this->redirect()->toUrl($retUrl);
					}
					else {
						$this->redirect()->toRoute('home');
					}
				}
				else{
					$err = _('Wrong login/password or your account is not activated');
				}
			}
		}

		return array(
			'form' => $form,
			'err' => $err,
		);
	}
	
	public function logoutAction() {
		session_destroy();
		return $this->redirect()->toRoute('home');
	}
}
