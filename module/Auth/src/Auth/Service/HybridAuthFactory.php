<?php

namespace Auth\Service;

use Zend\ServiceManager;
use Hybridauth\Hybridauth;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class HybridAuthFactory implements FactoryInterface
{
    /**
     * Create the service using the configuration from the modules config-file
     *
     * @param ServiceLocator $services The ServiceLocator
     *
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     * @return Hybrid_Auth
     */
    public function createService(ServiceLocatorInterface $services)
    {
        $config = $services->get('Config');
        $config = $config['Auth'];

        $config['hybrid_auth']['base_url'] = $this->getBackendUrl($services);


        $hybridAuth = new Hybridauth($config['hybrid_auth']);
        return $hybridAuth;
    }

    /**
     * Get the base URI for the current controller
     *
     * @return string
     */
    protected function getBackendUrl(ServiceLocatorInterface $sl)
    {
        $router = $sl->get('router');
        $route = $router->assemble(array(), array('name' => 'auth/backend'));

        $request = $sl->get('request');
        $basePath = $request->getBasePath();
        $uri = new \Zend\Uri\Uri($request->getUri());
        $uri->setPath($basePath);
        $uri->setQuery(array());
        $uri->setFragment('');

        //return $uri->getScheme() . '://' . $uri->getHost() . preg_replace('/[\/]+/', '/',  $uri->getPath() . '/' . $route);
        return $uri->getScheme() . '://' . $uri->getHost() . $route;
    }
}
