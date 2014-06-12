<?php
namespace Application\Lib;

class Acl extends \Zend\Permissions\Acl\Acl {
	/**
	 * Default Role
   */
  const DEFAULT_ROLE = 'guest';	

	public function __construct() {
		$this->addResource('Application\Controller\Index');
		$this->addResource('Application\Controller\User');
		
		$this->addResource('Admin\Controller\User');

		$guest = $this->addRole('guest');
		$user = $this->addRole('user', 'guest');
		$user = $this->addRole('admin', 'user');
		
		$this->allow('guest', 'Application\Controller\Index');
		$this->allow('guest', 'Application\Controller\User');
		$this->allow('admin', 'Admin\Controller\User');
		$this->allow('user', 'Admin\Controller\User', array(
			'index',
			'list',
			'edit',
		));
	}

}
