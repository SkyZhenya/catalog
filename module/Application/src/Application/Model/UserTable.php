<?php
namespace Application\Model;

use CodeIT\Model\CachedTable;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Debug;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Delete;

class UserTable extends CachedTable {

	use UserConf;
	use \Application\Traits\Avatar;
	
	/**
	 * user Id
	 * 
	 * @var integer
	 */
	public $id;

	/**
	 * Role in the system
	 * 
	 * @var string
	 */
	public $level;

	/**
	 * User name
	 * 
	 * @var string
	 */
	public $name = 'Guest';

	/**
	 * Whether user is active
	 * 
	 * @var integer
	 */
	public $active;

	/**
	 * Contact email
	 * 
	 * @var string
	 */
	public $email;

	/**
	 * Nickname for login
	 * 
	 * @var mixed
	 */
	public $login;

	/**
	 * Country
	 * 
	 * @var mixed
	 */
	public $country;

	/**
	 * Verification code
	 * 
	 * @var string
	 */
	public $code;

	/**
	 * new password value for reset password flow
	 * 
	 * @var string
	 */
	public $newpass;

	/**
	 * hash of user password
	 * 
	 * @var string
	 */
	public $password;

	/**
	 * Contact phone
	 * 
	 * @var string
	 */
	public $phone;
	
	/**
	 * avatar type, can be default or normal
	 * 
	 * @var string
	 */
	public $avatarType;
	
	/**
	 * array of links to avatars
	 * 
	 * @var array
	 */
	public $avatars;
	
	/**
	 * timestamp of last object update
	 * 
	 * @var int
	 */
	public $updated;

	/**
	 * List of fields from DB table
	 * 
	 * @var array
	 */
	protected $goodFields = array(
		'id',
		'name',
		'email',
		'level',
		'active',
		'created',
		'password',
		'newpass',
		'code',
		'phone',
		'birthdate',
		'updated',
	);

	public function __construct($userId = null) {
		parent::__construct('user', $userId);
	}

	/**
	 * returns row from db with specified id
	 *
	 * @param int $id
	 * @return \ArrayObject
	 */
	public function getUncached($id) {
		$row = parent::getUncached($id);

		$row->avatarType = 'normal';
		try {
			$imagesInfo = $this->getAvatar($id);
			$row->avatars = $imagesInfo['avatars'];
			$row->avatarsPaths = $imagesInfo['avatarsPaths'];
		}
		catch(\Exception $e) {
			// set default avatar
			$row->avatarType = 'default';

			foreach($this->avatarSizes as $sizes) {
				$row->avatars[$sizes[0]] = URL . 'images/user/default'.$sizes[0].'.png';
			}
		}

		return $row;
	}

	/**
	 * returns random user ids
	 *
	 * @param int $limit
	 * @param array $excludeUsers
	 * @return array
	 */
	public function findRandomUsers($limit=10, $excludeUsers=array()) {
		if(!empty($excludeUsers)) {
			$exclude = ' where id not in ('.implode(',', $excludeUsers).')';
		}
		$result = $this->query('select id from user'.$exclude.' order by rand() limit '.$limit);
		$users = array();
		foreach($result as $user) {
			$users []= $user->id;
		}

		return $users;
	}

	/**
	 * deletes user
	 *
	 * @param int $id
	 * @return bool: true on OK, false on user not found
	 */
	public function delete($id){
		$this->removeImages($id);
		
		return (bool)parent::delete(array('id' => $id));
	}

	/**
	 * returns name or login
	 * 
	 * @param mixed $id
	 * @param default return name
	 */
	public function getUserName($id,$login=false) {
		$row = $this->select(array('id' => $id))->current();
		if($login)
			return $row->login;
		else
			return $row->name;
	}

	/**
	 * check login data for user
	 * return user object if exists or false
	 * 
	 * @param string $email
	 * @param string $pass
	 * @return \ArrayObject|bool
	 */
	public function checkLogin($email, $pass){
		if(!$email || !$pass)return false;

		$row = $this->find(array(
			array('email', '=', $email),
			), 1 );

		if($row) {
			$row = array_pop($row);
			if ( password_verify($pass, $row->password)) {
				if (password_needs_rehash($row->password, PASSWORD_DEFAULT, ['cost' => PASSWORD_HASH_COST])) {
					$this->setId($row->id);
					$this->set([
						'password' =>  $this->passwordHash($pass),
					]);
				}
				return $row;
			}
		}
		return false;
	}

	/**
	 * create new user with not full data
	 * 
	 * @param mixed $data { email, pass } - required
	 */
	public function create($data, &$code = null) {

		$this->startTransaction();
		$code = \Application\Lib\Utils::generatePassword(32);

		$uid = parent::create([
			'name' => $data['name'],
			'password' => isset($data['password']) ? $this->passwordHash($data['password']) : '',
			'level' => isset($data['level']) ? $data['level'] : 'user',
			'email' => $data['email'],
			'code' => isset($code)? $code : null,
			'phone' => isset($data['phone'])? $data['phone'] : null,
			'created' => TIME,
			'gender' => isset($data['gender'])? $data['gender'] : null,
			'country' => isset($data['country'])? $data['country'] : '',
			'birthdate' => isset($data['birthdate'])? $data['birthdate'] : null,
		]);

		$this->commit();

		return $uid;
	}

	/**
	 * create hash code for password
	 * 
	 * @param string $pass
	 */
	public function passwordHash($pass){
		return password_hash($pass, PASSWORD_DEFAULT, ['cost' => PASSWORD_HASH_COST]);
	}

	/**
	 * send request to change password;
	 * user password will be changed only after confirmation by link
	 * 
	 * @param mixed $email
	 * @param mixed $newpass
	 */
	public function forgotPass($email, $newpass) {
		$UserRow = $this->select(array('email'	=> $email))->current();
		if(!$UserRow) {
			throw new \Exception(_('This E-mail is not found in our records'));
		}

		$this->setId($UserRow->id);
		$this->code = \Application\Lib\Utils::generatePassword(32);
		$this->newpass = $this->passwordHash($newpass);

		$this->set(array(
			'newpass' => $this->newpass,
			'code' => $this->code,
		));
	}

	/**
	 * save new password as user password for authentication
	 * after confirmation code check
	 * 
	 * @param int $id
	 * @param string $code
	 */
	public function activeForgotPass($id,$code){
		//get user
		$userRow = $this->select(array('id' => $id, 'code' => $code))->current();
		if(!$userRow) {
			throw new \Exception(_('Bad confirmation code'));
		}

		$this->setId($userRow->id);

		//set new pass
		$this->set(array(
			'password' => $userRow->newpass,
			'code' => '',
		));
	}

	/**
	 * activates user.
	 * 
	 * @param string $code
	 * @throws Exception on code not found
	 */
	public function activate($code) {
		$user = $this->find(array(array('code', '=', $code)))->current();

		if(!$user) {
			throw new \Exception(_('Wrong activation code or account already activated'));
		}

		$this->setId($user->id);
		$this->set(array(
			'active' => 1,
			'code' => '',
		));
	}

	/**
	 * find user account by email
	 * 
	 * @param string $email
	 * @return {\ArrayObject|bool}
	 */
	public function getByEmail($email = false){
		if($email == false) return false;
		$row = $this->find(array(
			array('email', '=', $email),
			), 1 );

		if($row) {
			$row = array_pop($row);
			return $row;
		}
		return false;
	}


}