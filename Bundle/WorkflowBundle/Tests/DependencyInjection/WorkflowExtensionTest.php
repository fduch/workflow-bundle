<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\WorkflowBundle\Tests\DependencyInjection;

use Symfony\Bundle\WorkflowBundle\DependencyInjection\WorkflowExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Reference;

abstract class WorkflowExtensionTest extends \PHPUnit_Framework_TestCase
{
    private static $containerCache = array();

    abstract protected function loadFromFile(ContainerBuilder $container, $file);

    public function testWorkflows()
    {
        $container = $this->createContainerFromFile('workflows');
        $this->assertTrue($container->hasDefinition('workflow.article', 'Workflow is registered as a service'));
        $this->assertTrue($container->hasDefinition('workflow.article.definition', 'Workflow definition is registered as a service'));
        $workflowDefinition = $container->getDefinition('workflow.article.definition');
        $this->assertSame(
            array(
                'draft',
                'wait_for_journalist',
                'approved_by_journalist',
                'wait_for_spellchecker',
                'approved_by_spellchecker',
                'published',
            ),
            $workflowDefinition->getArgument(0),
            'Places are passed to the workflow definition'
        );
        $this->assertSame(array('workflow.definition' => array(array('name' => 'article', 'type' => 'workflow', 'marking_store' => 'multiple_state'))), $workflowDefinition->getTags());
        $this->assertTrue($container->hasDefinition('state_machine.pull_request'), 'State machine is registered as a service');
        $this->assertTrue($container->hasDefinition('state_machine.pull_request.definition'), 'State machine definition is registered as a service');
        $this->assertCount(4, $workflowDefinition->getArgument(1));
        $this->assertSame('draft', $workflowDefinition->getArgument(2));
        $stateMachineDefinition = $container->getDefinition('state_machine.pull_request.definition');
        $this->assertSame(
            array(
                'start',
                'coding',
                'travis',
                'review',
                'merged',
                'closed',
            ),
            $stateMachineDefinition->getArgument(0),
            'Places are passed to the state machine definition'
        );
        $this->assertSame(array('workflow.definition' => array(array('name' => 'pull_request', 'type' => 'state_machine', 'marking_store' => 'single_state'))), $stateMachineDefinition->getTags());
        $this->assertCount(9, $stateMachineDefinition->getArgument(1));
        $this->assertSame('start', $stateMachineDefinition->getArgument(2));

        $serviceMarkingStoreWorkflowDefinition = $container->getDefinition('workflow.service_marking_store_workflow');
        /** @var Reference $markingStoreRef */
        $markingStoreRef = $serviceMarkingStoreWorkflowDefinition->getArgument(1);
        $this->assertInstanceOf(Reference::class, $markingStoreRef);
        $this->assertEquals('workflow_service', (string) $markingStoreRef);
    }
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage "type" and "service" cannot be used together.
     */
    public function testWorkflowCannotHaveBothTypeAndService()
    {
        $this->createContainerFromFile('workflow_with_type_and_service');
    }
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage "arguments" and "service" cannot be used together.
     */
    public function testWorkflowCannotHaveBothArgumentsAndService()
    {
        $this->createContainerFromFile('workflow_with_arguments_and_service');
    }


    protected function createContainer(array $data = array())
    {
        return new ContainerBuilder(new ParameterBag(array_merge(array(
            'kernel.bundles' => array('FrameworkBundle' => 'Symfony\\Bundle\\FrameworkBundle\\FrameworkBundle'),
            'kernel.cache_dir' => __DIR__,
            'kernel.debug' => false,
            'kernel.environment' => 'test',
            'kernel.name' => 'kernel',
            'kernel.root_dir' => __DIR__,
        ), $data)));
    }

    protected function createContainerFromFile($file, $data = array(), $resetCompilerPasses = true)
    {
        $cacheKey = md5(get_class($this).$file.serialize($data));
        if (isset(self::$containerCache[$cacheKey])) {
            return self::$containerCache[$cacheKey];
        }
        $container = $this->createContainer($data);
        $container->registerExtension(new WorkflowExtension());
        $this->loadFromFile($container, $file);

        if ($resetCompilerPasses) {
            $container->getCompilerPassConfig()->setOptimizationPasses(array());
            $container->getCompilerPassConfig()->setRemovingPasses(array());
        }
        $container->compile();

        return self::$containerCache[$cacheKey] = $container;
    }
}
