<?php

namespace Auth\Service;

use Auth\Service\UserWrapperFactory;
use CodeIT\Controller\AbstractController;
use Zend\View\Model\ViewModel;

/**
 * Login or out using a standart login form or social service
 */
abstract class AuthServiceController extends AbstractController
{
	/**
	 * Stores the Hybrid_Auth-Instance
	 *
	 * @var Hybrid_Auth $authenticator
	 */
	protected $authenticator = null;

	/**
	 * Storage of the UserProxyFactory
	 *
	 * @var UserWrapperFactory $userProxyFactory
	 */
	protected $userWrapperFactory = null;


	/**
	 * Get the authenticator
	 *
	 * @return Hybrid_Auth
	 */
	public function getAuthenticator()
	{
		if (empty($this->authenticator)) {
			$config = $this->getServiceLocator()->get('config');
			$config = $config['Auth'];

			$config['hybrid_auth']['base_url'] = $this->getBackendUrl($this->getServiceLocator());

			$hybridAuth = new \Hybrid_Auth($config['hybrid_auth']);
			$this->authenticator = $hybridAuth;
		}
		return $this->authenticator;
	}

	/**
	 * Get the userwrapper
	 *
	 * @return Auth\Service\UserWrapperFactory
	 */
	public function getUserWrapperFactory()
	{
		$this->userWrapperFactory = $this->getServiceLocator()->get('Auth\Service\UserWrapperFactory');
		return $this->userWrapperFactory;
	}

	/**
	 * Get the base URI for the current controller
	 *
	 * @return string
	 */
	protected function getBackendUrl(\Zend\ServiceManager\ServiceLocatorInterface $sl)
	{
		$router = $sl->get('router');
		$route = $router->assemble(array(), array('name' => 'auth/backend'));

		$request = $sl->get('request');
		$basePath = $request->getBasePath();
		$uri = new \Zend\Uri\Uri($request->getUri());
		$uri->setPath($basePath);
		$uri->setQuery(array());
		$uri->setFragment('');

		return $uri->getScheme() . '://' . $uri->getHost() . $route;
	}

}
