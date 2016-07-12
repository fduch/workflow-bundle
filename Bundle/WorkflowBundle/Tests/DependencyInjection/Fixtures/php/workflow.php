<?php

use Symfony\Bundle\FrameworkBundle\Tests\DependencyInjection\FrameworkExtensionTest;

$container->loadFromExtension('workflow', array(
    'workflows' => array(
        'my_workflow' => array(
            'marking_store' => array(
                'type' => 'property_accessor',
            ),
            'supports' => array(
                FrameworkExtensionTest::class,
            ),
            'places' => array(
                'first',
                'last',
            ),
            'transitions' => array(
                'go' => array(
                    'from' => array(
                        'first',
                    ),
                    'to' => array(
                        'last',
                    ),
                ),
            ),
        ),
    ),
));
