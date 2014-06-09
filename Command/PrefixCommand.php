<?php
namespace Mesd\DoctrineExtensions\TablePrefixerBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class PrefixCommand extends ContainerAwareCommand
{
    protected function configure() {
        $this
        ->setName( 'mesd:prefix' )
        ->setDescription( 'Show configured prefixes' )
        ;
    }

    protected function execute( InputInterface $input, OutputInterface $output ) {
        if ( $this->getContainer()->hasParameter( 'prefixed_bundles' ) ) {
            $output->writeln( "Prefixes" );
            $prefixed_bundles = $this->getContainer()->getParameter( 'prefixed_bundles' ) ;
            foreach ( $this->getContainer()->getParameter( 'prefixed_bundles' )  as $bundle => $prefixes ) {
                foreach ( $prefixes as $flavor => $stub ) {
                    if ( $stub ) {
                        $output->writeln( "$bundle / $flavor  => $stub" );
                    }
                }
            }
        } else {
            $output->writeln( "No prefixes or schema specified." );
        }
    }
}
