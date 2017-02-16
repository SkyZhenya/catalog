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
		$user = $this->addRole('manager', 'user');
		
		//Application module
		$this->addResource('Application\Controller\Index');
		$this->addResource('Application\Controller\Product');
		$this->addResource('Application\Controller\Favourite');
		$this->addResource('Application\Controller\Compare');
		
		$this->allow('guest', 'Application\Controller\Index');
		$this->allow('guest', 'Application\Controller\Product');
		$this->allow('user', 'Application\Controller\Favourite');
		$this->allow('guest', 'Application\Controller\Compare');
		
		//Admin module
		$this->addResource('Admin\Controller');//admin module generally, only for detecting if role has access to admin panel
		$this->addResource('Admin\Controller\User');
		$this->addResource('Admin\Controller\Template');
		$this->addResource('Admin\Controller\Category');
		$this->addResource('Admin\Controller\Product');
		
		$this->allow('admin', 'Admin\Controller');//only admin role has access to admin panel
		$this->allow('admin', 'Admin\Controller\User');
		$this->allow('admin', 'Admin\Controller\Template');
		$this->allow('admin', 'Admin\Controller\Category');
		$this->allow('admin', 'Admin\Controller\Product');

		//Manager access to admin panel
		$this->allow('manager', 'Admin\Controller');
		$this->allow('manager', 'Admin\Controller\User');
		$this->allow('manager', 'Admin\Controller\Category');
		$this->allow('manager', 'Admin\Controller\Product');

		//Auth module
		$this->addResource('Auth\Controller\Index');
		
		$this->allow('guest', 'Auth\Controller\Index');
	}

}
