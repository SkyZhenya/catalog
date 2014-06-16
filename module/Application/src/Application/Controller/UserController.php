<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Lib\AppController;

class UserController extends AppController {
  
  /**
   * login action
   *   
   */
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
					$this->user->login($user->id);
					
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
	
	/**
	 * logout action
	 * 
	 */
	public function logoutAction() {
		session_destroy();
		return $this->redirect()->toRoute('home');
	}
	
	/**
	 * send request to reset password
	 * 
	 */
	public function forgotpasswordAction() {
		if ($this->user->getId()) {
			return $this->redirect()->toRoute('home');
		}
		$success = false;
		$form = new \Application\Form\ForgotpasswordForm();
		
		if($this->request->isPost()){
			$data = $this->request->getPost();
			$form->setInputFilter($form->getFormInputFilter());
			$form->setData($data);
			if ($form->isValid()) {
				$data = $form->getData();
				try {
					$userTable = new \Application\Lib\User();
					$res = $userTable->forgotPass($data['email'], $data['password']);
					$success = true;
				}
				catch(\Exception $e) {
					$this->error = $e->getMessage();
				}
			}
		}
		
		return array(
			'form' => $form,
			'success' => $success,
		);
	}
	
	/**
	 * activate new password
	 * 
	 */
	public function activeforgotAction () {
		$id = $this->getParam('id');
		$code = $this->getParam('code');
		$mess = '';
		$err = null;
		try {
			$this->user->activeForgotPass($id, $code);
			$this->user->login($id);
			return $this->redirect()->toRoute('home');
		}
		catch(\Exception $e) {
			$err = $e->getMessage();
		}

    return array(
    	'mess' => $mess,
      'err' => $err
    );

	}
}
