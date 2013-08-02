<?php
namespace MESD\DoctrineExtensions\TablePrefixerBundle\Command;

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
        // ->addArgument(
        //     'name',
        //     InputArgument::OPTIONAL,
        //     'Who do you want to greet?'
        // )
        // ->addOption(
        //    'yell',
        //    null,
        //    InputOption::VALUE_NONE,
        //    'If set, the task will yell in uppercase letters'
        // )
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
