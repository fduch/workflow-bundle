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
            ->fixXmlConfig('workflow')
            ->children()
                ->arrayNode('workflows')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->fixXmlConfig('support')
                        ->fixXmlConfig('place')
                        ->fixXmlConfig('transition')
                        ->children()
                            ->arrayNode('audit_trail')
                                ->canBeEnabled()
                            ->end()
                            ->enumNode('type')
                                ->values(array('workflow', 'state_machine'))
                                ->defaultValue('workflow')
                            ->end()
                            ->arrayNode('marking_store')
                                ->fixXmlConfig('argument')
                                ->children()
                                    ->enumNode('type')
                                        ->values(array('multiple_state', 'single_state'))
                                    ->end()
                                    ->arrayNode('arguments')
                                        ->beforeNormalization()
                                            ->ifString()
                                            ->then(function ($v) { return array($v); })
                                        ->end()
                                        ->requiresAtLeastOneElement()
                                        ->prototype('scalar')
                                        ->end()
                                    ->end()
                                    ->scalarNode('service')
                                        ->cannotBeEmpty()
                                    ->end()
                                ->end()
                                ->validate()
                                    ->ifTrue(function ($v) { return isset($v['type']) && isset($v['service']); })
                                    ->thenInvalid('"type" and "service" cannot be used together.')
                                ->end()
                                ->validate()
                                    ->ifTrue(function ($v) { return !empty($v['arguments']) && isset($v['service']); })
                                    ->thenInvalid('"arguments" and "service" cannot be used together.')
                                ->end()
                            ->end()
                            ->arrayNode('supports')
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
                            ->scalarNode('support_strategy')
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('initial_place')->defaultNull()->end()
                            ->arrayNode('places')
                                ->isRequired()
                                ->requiresAtLeastOneElement()
                                ->prototype('scalar')
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()
                            ->arrayNode('transitions')
                                ->beforeNormalization()
                                    ->always()
                                    ->then(function ($transitions) {
                                        // It's an indexed array, we let the validation occurs
                                        if (isset($transitions[0])) {
                                            return $transitions;
                                        }

                                        foreach ($transitions as $name => $transition) {
                                            if (array_key_exists('name', $transition)) {
                                                continue;
                                            }
                                            $transition['name'] = $name;
                                            $transitions[$name] = $transition;
                                        }

                                        return $transitions;
                                    })
                                ->end()
                                ->isRequired()
                                ->requiresAtLeastOneElement()
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('name')
                                            ->isRequired()
                                            ->cannotBeEmpty()
                                        ->end()
                                        ->scalarNode('guard')
                                            ->cannotBeEmpty()
                                            ->info('An expression to block the transition')
                                            ->example('is_fully_authenticated() and has_role(\'ROLE_JOURNALIST\') and subject.getTitle() == \'My first article\'')
                                        ->end()
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
                        ->validate()
                            ->ifTrue(function ($v) {
                                return $v['supports'] && isset($v['support_strategy']);
                            })
                            ->thenInvalid('"supports" and "support_strategy" cannot be used together.')
                        ->end()
                        ->validate()
                            ->ifTrue(function ($v) {
                                return !$v['supports'] && !isset($v['support_strategy']);
                            })
                        ->thenInvalid('"supports" or "support_strategy" should be configured.')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
