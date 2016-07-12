<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\WorkflowBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Workflow;

/**
 * WorkflowExtension.
 *
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
class WorkflowExtension extends Extension
{
    /**
     * Responds to the app.config configuration parameter.
     *
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $this->registerWorkflowConfiguration($config['workflows'], $container, $loader);
    }

    /**
     * Loads the workflow configuration.
     *
     * @param array            $workflows A workflow configuration array
     * @param ContainerBuilder $container A ContainerBuilder instance
     * @param XmlFileLoader    $loader    An XmlFileLoader instance
     */
    private function registerWorkflowConfiguration(array $workflows, ContainerBuilder $container, XmlFileLoader $loader)
    {
        if (!$workflows) {
            return;
        }

        $loader->load('workflow.xml');

        $registryDefinition = $container->getDefinition('workflow.registry');

        foreach ($workflows as $name => $workflow) {
            $definitionDefinition = new Definition(Workflow\Definition::class);
            $definitionDefinition->addMethodCall('addPlaces', array($workflow['places']));
            foreach ($workflow['transitions'] as $transitionName => $transition) {
                $definitionDefinition->addMethodCall('addTransition', array(new Definition(Workflow\Transition::class, array($transitionName, $transition['from'], $transition['to']))));
            }

            if (isset($workflow['marking_store']['type'])) {
                $markingStoreDefinition = new DefinitionDecorator('workflow.marking_store.'.$workflow['marking_store']['type']);
                foreach ($workflow['marking_store']['arguments'] as $argument) {
                    $markingStoreDefinition->addArgument($argument);
                }
            } else {
                $markingStoreDefinition = new Reference($workflow['marking_store']['service']);
            }

            $workflowDefinition = new DefinitionDecorator('workflow.abstract');
            $workflowDefinition->replaceArgument(0, $definitionDefinition);
            $workflowDefinition->replaceArgument(1, $markingStoreDefinition);
            $workflowDefinition->replaceArgument(3, $name);

            $workflowId = 'workflow.'.$name;

            $container->setDefinition($workflowId, $workflowDefinition);

            foreach ($workflow['supports'] as $supportedClass) {
                $registryDefinition->addMethodCall('add', array(new Reference($workflowId), $supportedClass));
            }
        }
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/schema';
    }

    public function getNamespace()
    {
        return 'http://symfony.com/schema/dic/workflow';
    }
}
