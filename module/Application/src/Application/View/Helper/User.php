<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Application\Model\UserTable;

class User extends AbstractHelper {
	public function __invoke() {
		try {
			$user = \Zend\Registry::get('User');
		}
		catch(\Exception $e) {
			$user = new \Application\Lib\User();
			try {
				$user->auth(false);
			}
			catch(\Exception $e) {}
			\Zend\Registry::set('User', $user);
		}
		return $user;
	}
	public function getId() {
		return $id;
	}
}
