<?php

/**
 * VinariCore
 *
 * @link      https://github.com/TwoWholeWorms/VinariCore
 * @copyright Copyright (c) 2015 Vinari Ltd. (http://vinari.co.uk)
 * @license   BSD-3-Clause
 */

namespace VinariCore;

use Zend\Db\Adapter\Adapter;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

use Zend\Db\TableGateway\TableGateway;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Container;
use Zend\Session\SaveHandler\DbTableGatewayOptions;

use VinariCore\Session\SaveHandler\DbTableGateway;
use VinariCore\Session\SessionManager;
use Zend\Validator\ValidatorChain;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface, ServiceProviderInterface
{

    public function onBootstrap(MvcEvent $e)
    {
        if (php_sapi_name() !== 'cli') {
            $this->bootstrapSession($e);
        }

        $entityManager = $e->getApplication()->getServiceManager()->get('doctrine.entitymanager.orm_default');
        $entityManager->getFilters()->enable('soft_delete');
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
                'VinariCore\Mvc\Controller\AbstractActionController' => function (ServiceManager $sm) {
                    /** @var Adapter $adapter */
                    $adapter = $sm->get('Zend\\Db\\Adapter\\Adapter');

                    $tableGateway = new TableGateway('SessionEntity', $adapter);
                    $sessionSaveHandler = new DbTableGateway($tableGateway, new DbTableGatewayOptions());
                    return $sessionSaveHandler;
                },
                'Zend\\Session\\SaveHandler\\DbTableGateway' => function (ServiceManager $sm) {
                    /** @var Adapter $adapter */
                    $adapter = $sm->get('Zend\\Db\\Adapter\\Adapter');

                    $tableGateway = new TableGateway('SessionEntity', $adapter);
                    $sessionSaveHandler = new DbTableGateway($tableGateway, new DbTableGatewayOptions());
                    return $sessionSaveHandler;
                },
                'Zend\\Session\\SessionManager' => function (ServiceManager $sm) {
                    /** @var array $config */
                    $config = $sm->get('config');
                    if (array_key_exists('session', $config)) {
                        $session = $config['session'];

                        $sessionConfig = null;
                        if (array_key_exists('config', $session)) {
                            $class = array_key_exists('class', $session['config'])  ? $session['config']['class'] : 'Zend\\Session\\Config\\SessionConfig';
                            $options = array_key_exists('options', $session['config']) ? $session['config']['options'] : [];

                            /** @var SessionConfig $sessionConfig */
                            $sessionConfig = new $class();
                            $sessionConfig->setOptions($options);
                        }

                        $sessionStorage = null;
                        if (array_key_exists('storage', $session)) {
                            $class = $session['storage'];
                            $sessionStorage = new $class();
                        }

                        $sessionSaveHandler = null;
                        if (array_key_exists('save_handler', $session)) {
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

    public function bootstrapSession(MvcEvent $e)
    {
        $sessionManager = $e->getApplication()
                     ->getServiceManager()
                     ->get('Zend\\Session\\SessionManager');
        $sessionManager->start();
        Container::setDefaultManager($sessionManager);

        $sm = $e->getApplication()->getServiceManager();

        /** @var array $config */
        $config = $sm->get('Config');
        $container = new Container($config['session']['container_name']);

        if (!isset($container->init)) {
            $request        = $sm->get('Request');

            $sessionManager->regenerateId(true);
            $container->offsetSet('init', 1);
            $container->offsetSet('remoteAddr', $request->getServer()->get('REMOTE_ADDR'));
            $container->offsetSet('httpUserAgent', $request->getServer()->get('HTTP_USER_AGENT'));

            if (!array_key_exists('session', $config)) {
                return;
            }

            /** @var array $sessionConfig */
            $sessionConfig = $config['session'];
            if (array_key_exists('validators', $sessionConfig)) {
                /** @var ValidatorChain $chain */
                $chain   = $sessionManager->getValidatorChain();

                /** @var ValidatorChain $sessionValidator */
                $sessionValidator = $sm->get('session.validate');

                foreach ($sessionConfig['validators'] as $validator) {
                    switch ($validator) {
                        case 'Zend\\Session\\Validator\\HttpUserAgent':
                            $validator = new $validator($container->offsetGet('httpUserAgent'));
                            break;
                        case 'Zend\\Session\\Validator\\RemoteAddr':
                            $validator  = new $validator($container->offsetGet('remoteAddr'));
                            break;
                        default:
                            $validator = new $validator();
                    }

                    $chain->attach($sessionValidator, [$validator, 'isValid']);
                }
            }
        }
    }

}
