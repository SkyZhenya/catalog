<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Application\Model\UserTable;

class User extends AbstractHelper {
	public function __invoke() {
		try {
			$user = \Utils\Registry::get('User');
		}
		catch(\Exception $e) {
			$user = new \Application\Lib\User();
			try {
				$user->auth(false);
			}
			catch(\Exception $e) {}
			\Utils\Registry::set('User', $user);
		}
		return $user;
	}
}
