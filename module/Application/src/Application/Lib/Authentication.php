<?php
/**
* File for Event Class
*
* @category  User
* @package   User_Event
* @author    Marco Neumann <webcoder_at_binware_dot_org>
* @copyright Copyright (c) 2011, Marco Neumann
* @license   http://binware.org/license/index/type:new-bsd New BSD License
*/

/**
* @namespace
*/
namespace Application\Lib;

/**
* @uses Zend\Mvc\MvcEvent
* @uses User\Controller\Plugin\UserAuthentication
* @uses User\Acl\Acl
*/
use Zend\Mvc\MvcEvent as MvcEvent;
use User\Controller\Plugin\UserAuthentication as AuthPlugin;
use Zend\EventManager\StaticEventManager;
use Application\Lib\User;
use Application\Lib\AppController;

/**
* Authentication Event Handler Class
*
* This Event Handles Authentication
*
* @category  User
* @package   User_Event
* @copyright Copyright (c) 2011, Marco Neumann
* @license   http://binware.org/license/index/type:new-bsd New BSD License
*/
class Authentication {
	/**
	* @var \Zend\Cache\Pattern\ObjectCache
	*/
	protected $cachedAcl = null;

	/**
	* preDispatch Event Handler
	*
	* @param array $params
	* @param AppController $controller
	* @throws \Exception
	*/
	public function preDispatch($params, AppController $controller) {
		//@todo - Should we really use here and Controller Plugin?
		/**
		* @var User
		*/
		$user = \Zend\Registry::get('User');
		$acl = $this->getAclClass();
		$role = $user->getRole();

//		\Zend\Debug\Debug::dump($role);
//		\Zend\Debug\Debug::dump($params);
		if (!$acl->call('hasResource', array($params['controller']))) {
			throw new \Exception('Acl resource ' . $params['controller'] . ' not defined');
		}
				
		if (!$acl->call('isAllowed', array($role, $params['controller'], $params['action']))) {
			if($role == 'guest') {
					$url = URL.'user/login?r='.urlencode($_SERVER['REQUEST_URI']);
					header('HTTP/1.1 302 Found');
					header('Location: '.$url);
					exit;
			}
			else {
				$controller->forbiddenAction();
			}
		}
		
	}

	/**
	* Sets Authentication Plugin
	*
	* @param \User\Controller\Plugin\UserAuthentication $userAuthenticationPlugin
	* @return Authentication
	*/
	public function setUserAuthenticationPlugin(AuthPlugin $userAuthenticationPlugin) {
		$this->_userAuth = $userAuthenticationPlugin;

		return $this;
	}

	/**
	* Gets Authentication Plugin
	*
	* @return \User\Controller\Plugin\UserAuthentication
	*/
	public function getUserAuthenticationPlugin()
	{
		if ($this->_userAuth === null) {
			$this->_userAuth = new AuthPlugin();
		}

		return $this->_userAuth;
	}

	/**
	* Sets ACL Class
	*
	* @param \User\Acl\Acl $aclClass
	* @return Authentication
	*/
	public function setAclClass(AclClass $aclClass)
	{
		$this->_aclClass = $aclClass;

		return $this;
	}

	/**
	* Gets ACL Class
	*
	* @return \Application\Lib\Acl
	*/
	public function getAclClass() {
		if ($this->cachedAcl === null) {
			$cachedAcl = \Zend\Cache\PatternFactory::factory('object', array(
			  'object'   => new \Application\Lib\Acl(),
			  'storage' => 'memcached',
			  'object_key' => MEMCACHE_NAMESPACE.'.objectCache.\Application\Lib\Acl',
			  'cache_by_default' => false,

			  // the output don't need to be catched and cached
			  'cache_output' => false,
			));

			$this->cachedAcl = $cachedAcl;
		}

		return $this->cachedAcl;
	}
}
