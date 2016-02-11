<?php

/**
 * VinariCore
 *
 * @link      https://github.com/TwoWholeWorms/VinariCore
 * @copyright Copyright (c) 2015 Vinari Ltd. (http://vinari.co.uk)
 * @license   BSD-3-Clause
 */

namespace VinariCore;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

use Zend\Db\TableGateway\TableGateway;
use Zend\Mvc\MvcEvent;
use Zend\Session\Container;
use Zend\Session\Config\SessionConfig;
use Zend\Session\SaveHandler\DbTableGatewayOptions;
use Zend\Session\Storage\SessionArrayStorage;

use VinariCore\Session\SaveHandler\DbTableGateway;
use VinariCore\Session\SessionManager;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface, ServiceProviderInterface
{

    public function onBootstrap(MvcEvent $e)
    {
        $sm = $e->getApplication()->getServiceManager();
        $config = $sm->get('Config');

        if (php_sapi_name() != 'cli') {
            $this->bootstrapSession($e);
        }

        $entityManager = $e->getApplication()->getServiceManager()->get('doctrine.entitymanager.orm_default');
        $filter = $entityManager->getFilters()->enable("soft_delete");
    }

    public function getAutoloaderConfig()
    {

        return [
            'Zend\\Loader\\ClassMapAutoloader' => [
                __DIR__ . '/autoload_classmap.php',
            ],
            'Zend\\Loader\\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];

    }

    public function getConfig($env = null)
    {

        return include __DIR__ . '/config/module.config.php';

    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                'Zend\\Session\\SaveHandler\\DbTableGateway' => function ($sm) {
                    $config = $sm->get('Config');
                    $adapter = $sm->get('Zend\\Db\\Adapter\\Adapter');

                    $tableGateway = new TableGateway('SessionEntity', $adapter);
                    $sessionSaveHandler = new DbTableGateway($tableGateway, new DbTableGatewayOptions());
                    return $sessionSaveHandler;
                },
                'Zend\\Session\\SessionManager' => function ($sm) {
                    $config = $sm->get('config');
                    if (isset($config['session'])) {
                        $session = $config['session'];

                        $sessionConfig = null;
                        if (isset($session['config'])) {
                            $class = isset($session['config']['class'])  ? $session['config']['class'] : 'Zend\\Session\\Config\\SessionConfig';
                            $options = isset($session['config']['options']) ? $session['config']['options'] : [];
                            $sessionConfig = new $class();
                            $sessionConfig->setOptions($options);
                        }

                        $sessionStorage = null;
                        if (isset($session['storage'])) {
                            $class = $session['storage'];
                            $sessionStorage = new $class();
                        }

                        $sessionSaveHandler = null;
                        if (isset($session['save_handler'])) {
                            // class should be fetched from service manager since it will require constructor arguments
                            $sessionSaveHandler = $sm->get($session['save_handler']);
                        }

                        $sessionManager = new SessionManager($sessionConfig, $sessionStorage, $sessionSaveHandler);
                    } else {
                        $sessionManager = new SessionManager();
                    }

                    // $sessionManager->start();
                    Container::setDefaultManager($sessionManager);
                    return $sessionManager;
                },
            ],
        ];
    }

    public function bootstrapSession($e)
    {
        $sessionManager = $e->getApplication()
                     ->getServiceManager()
                     ->get('Zend\\Session\\SessionManager');
        $sessionManager->start();
        Container::setDefaultManager($sessionManager);

        $sm = $e->getApplication()->getServiceManager();

        $config = $sm->get('Config');
        $container = new Container($config['session']['container_name']);

        if (!isset($container->init)) {
            $serviceManager = $e->getApplication()->getServiceManager();
            $request        = $serviceManager->get('Request');

            $sessionManager->regenerateId(true);
            $container->init          = 1;
            $container->remoteAddr    = $request->getServer()->get('REMOTE_ADDR');
            $container->httpUserAgent = $request->getServer()->get('HTTP_USER_AGENT');

            $config = $serviceManager->get('Config');
            if (!isset($config['session'])) {
                return;
            }

            $sessionConfig = $config['session'];
            if (isset($sessionConfig['validators'])) {
                $chain   = $sessionManager->getValidatorChain();

                foreach ($sessionConfig['validators'] as $validator) {
                    switch ($validator) {
                        case 'Zend\\Session\\Validator\\HttpUserAgent':
                            $validator = new $validator($container->httpUserAgent);
                            break;
                        case 'Zend\\Session\\Validator\\RemoteAddr':
                            $validator  = new $validator($container->remoteAddr);
                            break;
                        default:
                            $validator = new $validator();
                    }

                    $chain->attach('session.validate', [$validator, 'isValid']);
                }
            }
        }
    }

}
