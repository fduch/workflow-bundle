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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * FrameworkExtension configuration structure.
 *
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('workflow');

        $rootNode
            ->children()
                ->arrayNode('workflows')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('marking_store')
                                ->isRequired()
                                ->children()
                                    ->enumNode('type')
                                        ->values(array('property_accessor', 'scalar'))
                                    ->end()
                                    ->arrayNode('arguments')
                                        ->beforeNormalization()
                                            ->ifString()
                                            ->then(function ($v) { return array($v); })
                                        ->end()
                                        ->prototype('scalar')
                                        ->end()
                                    ->end()
                                    ->scalarNode('service')
                                        ->cannotBeEmpty()
                                    ->end()
                                ->end()
                                ->validate()
                                    ->always(function ($v) {
                                        if (isset($v['type']) && isset($v['service'])) {
                                            throw new \InvalidArgumentException('"type" and "service" could not be used together.');
                                        }

                                        return $v;
                                    })
                                ->end()
                            ->end()
                            ->arrayNode('supports')
                                ->isRequired()
                                ->beforeNormalization()
                                    ->ifString()
                                    ->then(function ($v) { return array($v); })
                                ->end()
                                ->prototype('scalar')
                                    ->cannotBeEmpty()
                                    ->validate()
                                        ->ifTrue(function ($v) { return !class_exists($v); })
                                        ->thenInvalid('The supported class %s does not exist.')
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('places')
                                ->isRequired()
                                ->requiresAtLeastOneElement()
                                ->prototype('scalar')
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()
                            ->arrayNode('transitions')
                                ->useAttributeAsKey('name')
                                ->isRequired()
                                ->requiresAtLeastOneElement()
                                ->prototype('array')
                                    ->children()
                                        ->arrayNode('from')
                                            ->beforeNormalization()
                                                ->ifString()
                                                ->then(function ($v) { return array($v); })
                                            ->end()
                                            ->requiresAtLeastOneElement()
                                            ->prototype('scalar')
                                                ->cannotBeEmpty()
                                            ->end()
                                        ->end()
                                        ->arrayNode('to')
                                            ->beforeNormalization()
                                                ->ifString()
                                                ->then(function ($v) { return array($v); })
                                            ->end()
                                            ->requiresAtLeastOneElement()
                                            ->prototype('scalar')
                                                ->cannotBeEmpty()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
