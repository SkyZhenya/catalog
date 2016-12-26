<?php
namespace Application\Lib;

class Acl extends \Zend\Permissions\Acl\Acl {
	/**
	 * Default Role
	 */
	const DEFAULT_ROLE = 'guest';	

	public function __construct() {
		//user roles
		$guest = $this->addRole('guest');
		$user = $this->addRole('user', 'guest');
		$user = $this->addRole('admin', 'user');
		
		//Application module
		$this->addResource('Application\Controller\Index');
		
		$this->allow('guest', 'Application\Controller\Index');
		
		//Admin module
		$this->addResource('Admin\Controller');//admin module generally, only for detecting if role has access to admin panel
		$this->addResource('Admin\Controller\User');
		$this->addResource('Admin\Controller\Template');
		$this->addResource('Admin\Controller\Category');
		
		$this->allow('admin', 'Admin\Controller');//only admin role has access to admin panel
		$this->allow('admin', 'Admin\Controller\User');
		$this->allow('admin', 'Admin\Controller\Template');
		$this->allow('admin', 'Admin\Controller\Category');

		//Auth module
		$this->addResource('Auth\Controller\Index');
		
		$this->allow('guest', 'Auth\Controller\Index');
	}

}
