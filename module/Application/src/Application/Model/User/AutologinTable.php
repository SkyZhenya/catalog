<?php
namespace Application\Model\User;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use \Application\Model\AppTable;

class AutologinTable extends AppTable {
	
	protected $goodFields = array(
		'user',
		'token',
		'expire',
	);
	
	const ID_COLUMN = 'id';
	
	public function __construct() {
		parent::__construct('userAutologin');
		$this->deleteExpired();
	}
	
	/**
	* check if token is avlid
	* 
	* @param string $token
	* @return $userId
	*/
	public function checkToken($token) {
		if(!$token) return false;
		$userId = $this->query('select user from '.$this->table.' WHERE expire>? and token=?', [TIME, $token])->current();
		
		if (empty($userId)) 
			throw new \Exception(_('Wrong autologin token'));
		
		return $userId->user;
	}
	
	/**
	 * generate new autologin token and put it into DB
	 * 
	 * @param int $userId
	 */
	public function createToken($userId) {
		$token = \Application\Lib\Utils::generatePassword(64);
		$data = [
			'user' => $userId,
			'token' => $token,
			'expire' => TIME + REMEMBER_ME_PERIOD,
		];
		$this->insert($data);
		return $data;
	}
	
	/**
	 * deletes all expired records
	 * 
	 */
	public function deleteExpired() {
		$this->query('delete from '.$this->table.' where expire < ?', [TIME]);
	}
	
	/**
	 * deletes all specified user's tokens (usually called on logout)
	 * 
	 * @param int $userId
	 */
	public function deleteByUser($userId) {
		$this->query('delete from '.$this->table.' where user=?', [$userId]);
	}
	
}