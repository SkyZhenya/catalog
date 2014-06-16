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

		if(session_id() == '') {
			session_start();
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
	* Send message to current user
	* 
	* @param string $subject
	* @param string|\Zend\Mime\Message|object $text
	*/
	public function sendMail($subject, $text, $emailTo=null, $nameTo='') {
		if(!isset($emailTo) || empty($emailTo)) {
			if(!isset($this->email) || empty($this->email)) {
				throw new \Exception(_('E-Mail is not set or is empty'));
			}
			else {
				$emailTo = $this->email;
				$nameTo = $this->name;
			}
		}

    $m = new \Zend\Mail\Message();
    $m->addFrom(SUPPORT_EMAIL, SITE_NAME)
      ->addTo($emailTo, $nameTo)
      ->setSubject($subject);

    $bodyPart = new \Zend\Mime\Message();

    $bodyMessage = new \Zend\Mime\Part($text);
    $bodyMessage->type = 'text/html; charset=utf-8';

    $bodyPart->setParts(array($bodyMessage));

    $m->setBody($bodyPart);
    $m->setEncoding('UTF-8');
    
    $transport = new \Zend\Mail\Transport\Sendmail();
		$transport->send($m);
  }

  /**
   * save temporary password and send email to activate it
   * 
   * @param string $email
   * @param string $newpass
   */
	public function forgotPass($email, $newpass) {
		
		parent::forgotPass($email, $newpass);
		
		$template =  new Template();
		$tmplName = 'Forgot password';
		$tmplParams = array(
			'newpass' => $newpass,		
			'id' => $this->id,
			'code' => $this->code,
			'name' => $this->name,
		);	
		
		$message = $template->prepareMessage($tmplName, $tmplParams);
	  $this->sendMail($message['subject'], $message['text']);		
	}
	
	/**
	 * function makes all nesessary things on login
	 * 
	 * @param int $userId
	 */
	public function login($userId) {
		$this->setId($userId);
		
		$_SESSION['id'] = $userId;
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
  public function isActive(){
  	//verify user activity
		if (!$this->active){
			throw new \Exception(_('Cannot log you in: your account is blocked. Please contact Support'));
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
	
}
