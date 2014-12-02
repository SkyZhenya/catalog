<?php
namespace Auth;

return array(
    'router' => array(
        'routes' => array(
            'auth' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/auth',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Auth\Controller',
                        'controller' => 'Index',
                        'action'     => 'login',
                    ),
                    'constraints' => array(
						'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
					),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                	'login' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/login[/]',
                            'defaults' => array(
                                'action'   => 'login',
                            ),
                        ),
                    ),
                    'registration' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/registration[/]',
                            'defaults' => array(
                                'action'   => 'registration',
                            ),
                        ),
                    ),
                    'forgotpassword' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/forgotpassword[/]',
                            'defaults' => array(
                                'action'   => 'forgotpassword',
                            ),
                        ),
                    ),
                    'google' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/google[/]',
                            'defaults' => array(
                                'action'   => 'socialNetworkLogin',
                                'provider' => 'google',
                            ),
                        ),
                    ),
                    
                    'facebook' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/facebook[/]',
                            'defaults' => array(
                                'action'   => 'socialNetworkLogin',
                                'provider' => 'facebook',
                            ),
                        ),
                    ),
                    
                    'logout' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/logout[/]',
                            'defaults' => array(
                                'action' => 'logout',
                            ),
                        ),
                    ),
					'backend' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/backend',
                            'defaults' => array(
                                'action' => 'backend',
                            ),
                        ),
                    ),
                    'activeforgot' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/activeforgot/:id/:code[/]',
                            'defaults' => array(
                                'action' => 'activeforgot',
                            ),
                            'constraints' => array(
                                'code' => '[a-zA-Z0-9_-]*',
                                'id' => '[0-9]*',
                            ),
                        ),
                    ),
                    'complete-registration' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/complete-registration[/]',
                            'defaults' => array(
                                'action' => 'completeRegistration',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'controllers' => array(
    	'factories' => array(
            'Auth\Controller\Index' => 'Auth\Service\IndexControllerFactory',
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'AuthBackend' => 'Auth\Service\HybridAuthFactory',
            'AuthCurrentUser' => 'Auth\Service\UserFactory',
        ),
        'invokables' => array(
            'Auth\Service\UserWrapperFactory' => 'Auth\Service\UserWrapperFactory',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'authinfo' => 'Auth\View\Helper\Auth',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => DEBUG,
        'display_exceptions' => DEBUG,
        'doctype' => 'HTML5',
        'not_found_template' => 'Auth/error/404',
        'exception_template' => 'Auth/error/index',
        'template_map' => array(
            'Auth/error/404'               => __DIR__ . '/../view/error/404.phtml',
            'Auth/error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
);
