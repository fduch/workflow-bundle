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

abstract class WorkflowExtensionTest extends \PHPUnit_Framework_TestCase
{
    private static $containerCache = array();

    abstract protected function loadFromFile(ContainerBuilder $container, $file);


    public function testWorkflow()
    {
        $container = $this->createContainerFromFile('workflow');

        $this->assertTrue($container->hasDefinition('workflow.my_workflow'));
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
