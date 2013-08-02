<?php

namespace MESD\DoctrineExtensions\TablePrefixerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class MESDDoctrineExtensionsTablePrefixerExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load( array $configs, ContainerBuilder $container ) {
        $configuration = new Configuration();
        $config = $this->processConfiguration( $configuration, $configs );

        $loader = new Loader\YamlFileLoader( $container, new FileLocator( __DIR__.'/../Resources/config' ) );
        $loader->load( 'services.yml' );

        foreach ( $config as $label => $bundles ) {
            if ( 'prefixed_bundles' == $label )
            foreach ( $bundles as $labl => $stub ) {

                foreach ( $stub as $type => $amend ) {
                    if (!$amend) {
                        $bundles[$labl][$type] = '';
                    }
                $container->setParameter('prefixed_bundles', $bundles);
                }
            }
        }
    }
}
