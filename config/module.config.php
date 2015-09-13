<?php

return [
    'view_manager' => [
        'template_path_stack' => [
            'vinari-core' => __DIR__ . '/../view',
        ],
    ],
    'router' => [
        'routes' => [
            // …
        ],
    ],
    'controllers' => [
        'invokables' => [
            'VinariCore\\Controller\\Console\\Email' => 'VinariCore\\Controller\\Console\\EmailController',
        ],
    ],
    'doctrine' => [
        'driver' => [
            'vinaricore_entities' => [
                'class' => 'Doctrine\\ORM\\Mapping\\Driver\\AnnotationDriver',
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/VinariCore/Entity',
                ],
            ],
            'orm_default' => [
                'drivers' => [
                    'VinariCore\\Entity' => 'vinaricore_entities',
                ],
            ],
        ],
        'configuration' => [
            'orm_default' => [
                'filters' =>  [
                    'soft_delete' => 'VinariCore\\Filter\\Deleted',
                ],
            ],
        ],
    ],
    'data-fixture' => [
        'VinariCore_fixture' => __DIR__ . '/../src/VinariCore/Fixture',
    ],
    'service_manager' => [
        'factories' => [
            'Vinari\\Common\\Cache\\Cache' => function ($sm) {
                $model = new \Vinari\Common\Cache\Cache();
                $model->setServiceLocator($sm);

                return $model;
            },
        ],
    ],
    'zfctwig' => [
        'extensions' => [
            \VinariCore\Twig\VinariTwigExtension::class,
        ],
    ],
    'console' => [
        'router' => [
            'routes' => [
                'email' => [
                    'options' => [
                        'route'    => 'send-emails [--dry-run] [--limit|-l]',
                        'defaults' => [
                            'controller' => 'VinariCore\\Controller\\Console\\Email',
                            'action'     => 'send',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
