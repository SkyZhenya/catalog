<?php

namespace Auth\Service;

use \Zend\ServiceManager;
use \Zend\Session\Container as SessionContainer;
use \Auth\Controller\IndexController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Create an instance of the session
 */
class IndexControllerFactory implements FactoryInterface
{
    /**
     * Create the service using the configuration from the modules config-file
     *
     * @param ServiceLocator $serviceLocator The ServiceLocator
     *
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     * @return Hybrid_Auth
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();

        $authenticator  = $serviceLocator->get('AuthBackend');
        $wrapperFactory = $serviceLocator->get('Auth\Service\UserWrapperFactory');

        $controller = new IndexController();
        $controller->setAuthenticator($authenticator)
                   ->setUserWrapperFactory($wrapperFactory);
        return $controller;
    }
}
