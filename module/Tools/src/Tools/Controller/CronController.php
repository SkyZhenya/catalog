<?php
namespace Tools\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Db\Sql\Expression;

class CronController extends AbstractActionController {

	public function everyHourAction() {
	}
	
	public function everyDayAction() {
		$userTable = new \Application\Lib\User();
		$totalNewUsers = 0;
		$newUsers = $userTable->find([
			['created', '>=', mktime(0, 0, 0, date('m', TIME), date('d', TIME) - 7, date('Y', TIME))],
		], 1, 0, false, $totalNewUsers);
		
		if ($totalNewUsers) {
			//get first admin
			$admin = $userTable->find([
				['level', '=', 'admin'],
				['active', '=', 1],
			], 1, 0, 'id asc');
			
			if ($admin) {
				$admin = array_pop($admin);
				$userTable->setId($admin->id);
				$email = new \Application\Lib\Email();
				$email->sendTemplate('New users report', $userTable, [
					'number' => $totalNewUsers,
				]);
			}
		}
		return;
	}

}
