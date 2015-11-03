<?php
namespace Application\Lib;
use \Application\Model\UserTable;
use \Application\Lib\Template;

class User extends UserTable {

	var $acl;

	/**
	 * user authorization
	 * $forceAuth shows if this application is strictly for authorized users
	 * 
	 * @param bool $forceAuth
	 * @return bool
	 */
	public function auth($forceAuth = true) {

		if (defined('SESSION_NAME')) {
			session_name(SESSION_NAME);
		}
		
		if(session_id() == '') {
			session_start();
		}

		if(!isset($_SESSION['id'])) {
			$this->loginByAutologin();
		}
		
		if(isset($_SESSION['id'])) {
			try {
				$this->setId($_SESSION['id']);
				$this->isActive();
				return true;
			}
			catch(\Exception $e) {
				@session_destroy();
				throw new \Exception(_('Cannot log you in: your account is blocked or server error encountered. Please contact Support'));
			}
		}

		if($forceAuth) {
			throw new \Exception(_('Server requires you should be logged in to access this area'));
		}

		return false;
	}

	/**
	 * save temporary password and send email to activate it
	 * 
	 * @param string $email
	 * @param string $newpass
	 */
	public function forgotPass($email, $newpass) {

		parent::forgotPass($email, $newpass);

		//send email
		$emailLib = new \Application\Lib\Email();
		$emailLib->sendTemplate('Forgot password', $this, [
			'newpass' => $newpass,		
			'id' => $this->id,
			'code' => $this->code,
			'name' => $this->name,
		]);
	}



	/**
	 * returns current user role
	 * 
	 * @return string
	 */
	public function getRole(){
		$role = Acl::DEFAULT_ROLE;

		//\Zend\Debug\Debug::dump($user);
		if ($this->getId()) {

			$role = $this->level;
		}

		return $role;

	}

	/**
	 * verify user account activity
	 * if user is blocked - throw exception and don't log in user
	 * 
	 */
	public function isActive($user = null){
		if (is_null($user)) {
			$user = $this;
		}
		//verify user activity
		if (!$user->active){
			$user->id = null;
			$user->level = \Application\Lib\Acl::DEFAULT_ROLE;
			throw new \Exception(_('Your account was blocked by Administration'));
		}

		return true;
	}


	/**
	 * method returns if some resorce is allowed to current user, using Acl
	 * 
	 * @param string $resource
	 * @param string $action
	 * @param string $role
	 * 
	 * @return bool
	 */
	public function isAllowed($resource = null, $action = null, $role = null) {
		$acl = $this->getAcl();
		if(is_null($role)) {
			$role = $this->level;
		}
		if ($acl->hasResource($resource)) {
			return $acl->isAllowed($role, $resource, $action);
		}
		else {
			return false;
		}
	}

	/** returns acl object from object storage or create new
	 * 
	 */
	public function getAcl(){
		if (empty($this->acl)){
			$this->acl = new \Application\Lib\Acl();
		}
		return $this->acl;
	}
	
	/**
	 * function makes all nesessary things on login
	 * 
	 * @param int $userId
	 */
	public function login($userId) {
		$this->setId($userId);
		$this->isActive();
		$_SESSION['id'] = $userId;
		setcookie('LoggedIn', 1, NULL, '/', '.' . DOMAIN, null, true);
	}

	/**
	 * function makes all nesessary things on logout
	 * 
	 * @param int $id
	 */
	public function logout($id = 0) {
		if (empty($id))
			$id = $this->id;
		// kill all their autologin tokens
		$userAutologinTable = new \Application\Model\User\AutologinTable();
		$userAutologinTable->deleteByUser($id);
		
		session_destroy();
		setcookie('LoggedIn', NULL, -1, '/', '.' . DOMAIN, null, true);
		setcookie('autologin', NULL, -1, '/', '.' . DOMAIN, null, true);
	}
	
	/**
	 * stores cookie for autologin feature
	 * 
	 * @param int $userId
	 */
	public function rememberMe($userId) {
		$userAutologinTable = new \Application\Model\User\AutologinTable();
		$tokenData = $userAutologinTable->createToken($userId);
		setcookie('autologin', $tokenData['token'], $tokenData['expire'], '/', '.' . DOMAIN, null, true);
	}

	/**
	 * logs user in by their autologin id
	 * 
	 */
	public function loginByAutologin() {
		if(isset($_COOKIE['autologin'])) {
			try {
				$userAutologinTable = new \Application\Model\User\AutologinTable();
				$userId = $userAutologinTable->checkToken($_COOKIE['autologin']);
				$this->login($userId);
				return true;
			}
			catch(\Exception $e) {
				unset($_COOKIE['autologin']);
				setcookie('autologin', NULL, -1, '/', '.' . DOMAIN, null, true);
			}
		}

		return false;
	}
}
