<?php
namespace Application\Model\User;

use CodeIT\Model\AppTable;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Debug;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Delete;

class SocialnetworkTable extends AppTable {
	/**
	 * user Id
	 * 
	 * @var integer
	 */
	public $userId;
	
	/**
	 * user identifier in social network
	 * 
	 * @var string
	 */
	public $identifierId;

	/**
	 * social network name
	 *
	 * @var string
	 */
	public $provider;   

	protected $goodFields = array(
		'id',
		'userId',
		'identifierId',
		'provider',
	);

	public function __construct($userId = null) {
		parent::__construct('userSocialNetwork', $userId);
	}


	public function checkProviderIdentity($identifierId, $provider){
		$row = $this->find(array(
			array('identifierId', '=', $identifierId),
			array('provider', '=', $provider)
			), 1 );

		if(count($row)) {
			$row = $row->current();
			return $row;
		}

		return false;
	}


}