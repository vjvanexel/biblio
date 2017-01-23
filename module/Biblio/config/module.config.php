<?php
/**
 * Created by PhpStorm.
 * User: vjvan
 * Date: 1/17/2017
 * Time: 22:50
 */
namespace Biblio;

use Zend\Router\Http\Segment;

return [
    'router'=> [
        'routes' => [
            'biblio' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/biblio[/:action[/:id]]',
                    'defaults' => [
                        'controller' => Controller\BiblioController::class,
                        'action' => 'index'
                    ]
                ]
            ]
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
            'biblio'        => __DIR__ . '/../view',
            'layout/layout' => __DIR__ . '/../view',
            'biblio/layout' => __DIR__ . '/../view/layout/layout.phtml',
        ],
        'template_map' => [
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'biblio/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'error/404'     => __DIR__ . '/../view/error/404.phtml',
            'error/index'   => __DIR__ . '/../view/error/index.phtml',
        ],
    ],
];