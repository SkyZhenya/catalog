<?php

namespace Auth\Controller;

use Application\Model\User\SocialnetworkTable;
use Application\Lib\User;
use Application\Lib\Image;
use Auth\Service\UserWrapperFactory;
use Auth\Service\AuthServiceController;
use Zend\View\Model\ViewModel;

/**
 * Login or out using a standart login form or social service
 */
class IndexController extends AuthServiceController
{
	/**
	 *
	 * @var \Application\Model\User\SocialnetworkTable
	 */
	var $socialnetworkTable;

	/**
	 *
	 * @var \Application\Lib\User
	 */
	var $userTable;

	public function ready() {
		parent::ready();
		$this->socialnetworkTable = new SocialnetworkTable();
		$this->userTable = new User();
	}

	/**
	 * login using login & password
	 */
	public function loginAction() {
		if($this->user->getId()) {
			return $this->redirect()->toRoute('home');
		}

		$form = new \Auth\Form\LoginForm();

		if($this->request->isPost()){
			$data = $this->request->getPost();
			$form->setData($data);
			if ($form->isValid()) {
				$data = $form->getData();
				$user = $this->user->checkLogin($data['email'], $data['password']);

				if($user) {
					try {
						$profile = $this->user->login($user->id);
						if ($data['rememberme']) {
							$this->user->rememberMe($user->id);
						}
						return $this->redirect()->toRoute('home');
					}
					catch (\Exception $e) {
						$this->error = $e->getMessage();
					}
				}
				else{
					$form->get('email')->setMessages(array(
						'notvalid' => _('Login credentials are incorrect'),
					));
				}
			}
		}

		$result = new ViewModel(array(
			'form' => $form,
			'error' => $this->error,
		));
		$result->setTemplate('auth/index/login');
		return $result;

	}

	/**
	 * social login via fb, google
	 * 
	 */
	public function socialNetworkLoginAction() {
		if($this->user->getId()) {
			return $this->redirect()->toRoute('home');
		}

		$provider = $this->params()->fromRoute('provider');
		$user = null;

		if($provider) {
			try {
				$this->authViaSocialNetwork($provider);
				//Authorization: Successful
				return $this->redirect()->toRoute('home');
			} catch (\Exception $e) {
				$this->error = $e->getMessage();
			}
		}

		$result = new ViewModel(array(
			'form' => new \Auth\Form\LoginForm(),
			'error' => $this->error,
		));
		$result->setTemplate('auth/index/login');
		return $result;

	}

	/**
	 * get user profile from social network and register or login user
	 * 
	 * @param string $provider
	 */
	protected function authViaSocialNetwork($provider) {
		try {
			$backend = $this->getAuthenticator()->authenticate($provider);

			/** @var \Hybrid_User_Profile $profile */
			$profile = $backend->getUserProfile();

			$userProfileDb = $this->socialnetworkTable->checkProviderIdentity($profile->identifier, $provider);
			$userProvider = $this->getUserWrapperFactory()->factory($profile);
			//if this user-profile not register
			if($userProfileDb == false) {
				//if this email-profile used
				$user = $this->user->getByEmail($profile->email);
				if($user) {
					throw new \Exception(sprintf(_("This email is already used, please authorized by %s and merge profiles"), $profile->email));
				}
				//create user
				$userId = $this->userTable->create(array(
					'email' => $userProvider->getMail(),
					'name' => $userProvider->getName(),
					'birthdate' => (!empty($userProvider->getBirthYear())? date('Y-m-d', mktime(0, 0, 0, $userProvider->getBirthMonth(), $userProvider->getBirthDay(), $userProvider->getBirthYear())) : null),
				));
				if (!empty($userProvider->getPhotoURL())) {
					$avatarTmp = tempnam(sys_get_temp_dir(), 'user_');
					Image::simpleImageUpload($userProvider->getPhotoURL(), $avatarTmp);
					$this->user->setAvatar($avatarTmp, $userId);
				}
				//create profile
				$this->socialnetworkTable->insert([
					'userId' => $userId,
					'provider' => $provider,
					'identifierId' => $userProvider->getUID(),
				]);

				$this->user->login($userId);
			}
			else {
				$this->user->login($userProfileDb->userId);
			}
		}
		catch( Exception $e ){
			throw new \UnexpectedValueException(_('User is not connected'));
		}
	}

	/**
	 * Logout
	 */
	public function logoutAction() {
		$this->user->logout();
		$this->redirect()->toRoute('home');
	}

	/**
	 * Call the HybridAuth-Backend
	 */
	public function backendAction() {
		$endpoint = new \Hybrid_Endpoint();
		try {
			$endpoint->process();
		}
		catch (\Exception $e) {
			$this->error = $e->getMessage();
			return $this->loginAction();
		}
		return false;
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
		$form = new \Auth\Form\ForgotpasswordForm();

		if($this->request->isPost()){
			$data = $this->request->getPost();
			$form->setData($data);
			if ($form->isValid()) {
				$data = $form->getData();
				try {
					$userTable = new \Application\Lib\User();
					$res = $userTable->forgotPass($data['email'], $data['newpassword']);
					$success = true;
				}
				catch(\Exception $e) {
					$form->get('email')->setMessages(array($e->getMessage()));
				}
			}
		}

		return array(
			'form' => $form,
			'success' => $success,
		);
	}

	/**
	 * save new password as current
	 * 
	 */
	public function activeforgotAction() {
		$id = $this->params()->fromRoute('id');
		$code =  $this->params()->fromRoute('code');
		$err = '';
		try {
			$this->user->activeForgotPass($id, $code);
			$profile = $this->user->login($id);
			return $this->redirect()->toRoute('home');
		}
		catch(\Exception $e) {
			$err = $e->getMessage();
		}

		return array(
			'err' => $err
		);
	}

	/**
	 * register profile
	 * 
	 */
	public function registrationAction() {
		$form = new \Auth\Form\RegistrationForm();
		if($this->request->isPost()){
			$form->setData($this->request->getPost());

			if ($form->isValid()) {
				$profileData = $form->getData();
				try {
					$profileData['birthdate'] = date('Y-m-d', mktime(0, 0, 0, $profileData['birthmonth'], $profileData['birthday'], $profileData['birthyear']));
					$this->user->create($profileData);
					$this->user->login($this->user->getId());
					return $this->redirect()->toRoute('home');
				}
				catch (\Exception $e) {
					$this->error = $e->getMessage();
				}
			}
		}

		return array(
			'form' => $form,
			'error' => $this->error,
		);
	}

}
