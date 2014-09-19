<?php

namespace Mesd\DoctrineExtensions\TablePrefixerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder() {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root( 'mesd_doctrine_extensions_table_prefixer' );

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        $rootNode
            ->children()
                ->arrayNode('prefixed_bundles')
                ->useAttributeAsKey('bundle')
                ->prototype('array')
                    ->children()
                        ->scalarNode('schema')->end()
                        ->scalarNode('prefix')->end()
                        ->scalarNode('flatten')->end()
                    ->end()
                ->end()
            ->end()
        ;


        return $treeBuilder;
    }
}
