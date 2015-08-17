<?php
namespace Application\Model;

//config lists for search, profiles etc.
trait UserConf {
	
	public $avatarSizes = [
		[100, 100], // small square thumb 
		[140, 140], // square medium thumb
		[250, 250], // standart big square avatar
	];
	
	/**
	 * list of user roles
	 * 
	 * @var array
	 */
	public static $roleDescriptions = array(
		'user' => 'User',
		'admin' => 'Admin',
	);

}

?>
