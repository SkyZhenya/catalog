<?php

namespace Auth\Controller;

use Hybridauth\Hybridauth;
use Hybridauth\Endpoint;
use Auth\Service\UserWrapperFactory;
use Zend\View\Model\ViewModel;
use Application\Lib\AppController;
use Application\Model\User\SocialnetworkTable;
use Application\Lib\User;

/**
 * Login or out using a standart login form or social service
 */
class IndexController extends AppController
{
	/**
	 * Stores the HybridAuth-Instance
	 *
	 * @var Hybridauth $authenticator
	 */
	protected $authenticator = null;

	/**
	 * Storage of the UserProxyFactory
	 *
	 * @var UserWrapperFactory $userProxyFactory
	 */
	protected $userWrapperFactory = null;

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
	 * Set the authenticator
	 *
	 * @param Hybrid_Auth $authenticator The Authenticator-Backend
	 *
	 * @return IndexController
	 */
	public function setAuthenticator(Hybridauth $authenticator)
	{
		$this->authenticator = $authenticator;
		return $this;
	}

	/**
	 * Set the userwrapper
	 *
	 * @param UserWrapperFactory $factory The ProxyFactory
	 *
	 * @return IndexController
	 */
	public function setUserWrapperFactory(UserWrapperFactory $factory)
	{
		$this->userWrapperFactory = $factory;
		return $this;
	}

	/**
	 * login using login & password
	 */
	public function loginAction()
	{
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
						$this->user->isActive($user);
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
	public function socialNetworkLoginAction()
	{
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
		$backend = $this->authenticator->authenticate($provider);

		if (! $backend->isAuthorized()) {
			throw new \UnexpectedValueException('User is not connected');
		}
		/** @var \Hybridauth\Entity\profile $profile */
		$profile = $backend->getUserProfile();

		$userProfileDb = $this->socialnetworkTable->checkProviderIdentity($profile->getUID(), $provider);
		$userProvider = $this->userWrapperFactory->factory($profile);
		//if this user-profile not register
		if($userProfileDb == false) {
			//if this email-profile used
			$user = $this->user->getByEmail($profile->getEmail());
			if($user) {
				throw new \Exception(sprintf(_("This email is already used, please authorized by %s and merge profiles"), $profile->getEmail()));
			}
			//create user
			$userId = $this->userTable->create(array(
				'email' => $userProvider->getMail(),
				'name' => $userProvider->getName(),
				'birthdate' => (!empty($userProvider->getBirthYear())? date('Y-m-d', mktime(0, 0, 0, $userProvider->getBirthMonth(), $userProvider->getBirthDay(), $userProvider->getBirthYear())) : null),
			));
			//create profile
			$this->socialnetworkTable->insert([
				'userId' => $userId,
				'provider' => $provider,
				'identifierId' => $userProvider->getUID(),
			]);
			if (!empty($userProvider->getPhotoURL())) {
				$avatarTmp = $this->user->uploadSNAvatar($userProvider->getPhotoURL());
				if (!empty($avatarTmp))
					$userData = $this->userTable->setAvatar($avatarTmp, $userId);
			}
			$this->user->login($userId);
		}
		else {
			$this->user->login($userProfileDb->userId);
		}
	}

	/**
	 * Logout
	 */
	public function logoutAction()
	{
		$this->user->logout();
		$this->redirect()->toRoute('home');
	}

	/**
	 * Redirect to the last known URL
	 *
	 * @return boolean
	 */
	protected function doRedirect()
	{
		$redirect = base64_decode($this->getEvent()->getRouteMatch()->getParam('redirect'));
		// var_dump($redirect);exit;

		if (preg_match('|://|', $redirect)) {
			$this->redirect()->toUrl($redirect);
		} else {
			$this->redirect()->toRoute($redirect);
		}

		return false;
	}

	/**
	 * Call the HybridAuth-Backend
	 */
	public function backendAction()
	{
		$endpoint = new Endpoint();
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
	public function activeforgotAction () {
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
