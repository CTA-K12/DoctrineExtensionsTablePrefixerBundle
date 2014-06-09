<?php
namespace Mesd\DoctrineExtensions\TablePrefixerBundle\Subscriber;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;

class TablePrefixSubscriber implements \Doctrine\Common\EventSubscriber
{
    private $bundles;
    private $defaults;

    public function __construct( $container ) {
        if ( $container->hasParameter( 'prefixed_bundles' ) ) {
            $this->bundles = $container->getParameter( 'prefixed_bundles' ) ;
            $this->defaults = null;
            if ( isset( $this->bundles['default'] ) ) { $this->defaults=$this->bundles['default'];}
        } else {

        }
    }

    public function getSubscribedEvents() {
        return array( 'loadClassMetadata' , 'prePersist' );
    }


    public function prePersist( LifecycleEventArgs $eventArgs) {
        $classMetadata=$eventArgs->getEntityManager()
            ->getClassMetadata(get_class($eventArgs->getEntity()));
        $this->prefix($classMetadata);
    }

    public function loadClassMetadata( LoadClassMetadataEventArgs $eventArgs ) {
        $classMetadata = $eventArgs->getClassMetadata();
        $this->prefix($classMetadata);
    }

    public function prefix($classMetadata){
        $blank=( 'blank' == $this->defaults['schema'] );
        $bundle=strstr( $classMetadata->getName(), 'Bundle\\', True ).'Bundle';
        $prefix=isset( $this->bundles[$bundle]['prefix'] )?$this->bundles[$bundle]['prefix']:null;
        $schema=isset( $this->bundles[$bundle]['schema'] )?$this->bundles[$bundle]['schema']:null;
        $sequenceGeneratorDefinition=$classMetadata->sequenceGeneratorDefinition;

        if ( $this->defaults || !isset( $this->bundles[$bundle] ) ) {
            $prefix=( is_null( $prefix )?$this->defaults['prefix']:$prefix );
            $schema=( is_null( $schema )?$this->defaults['schema']:$schema );
        }

        foreach ( $classMetadata->getAssociationMappings() as $fieldName => $mapping ) {
            if ( $mapping['type'] == \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY ) {
                if ( $classMetadata->associationMappings[$fieldName]['isOwningSide'] ) {
                    $mappedTableName = $classMetadata->associationMappings[$fieldName]['joinTable']['name'];
                    if ( $blank ) {
                        if ( false !== strpos( $classMetadata->getTableName(), '.' ) ) {
                            $classMetadata->associationMappings[$fieldName]['joinTable']['name']
                                = str_replace( '.', '', strstr( $mappedTableName, '.' ) )
                            ;
                        }
                    } else {
                        $classMetadata->associationMappings[$fieldName]['joinTable']['name']
                            = $prefix . $mappedTableName;
                        if ( $schema ) {
                            if ( false === strpos( $mappedTableName, '.' ) ) {
                                $classMetadata->associationMappings[$fieldName]['joinTable']['name']
                                    = $schema . '.' . $mappedTableName ;
                            } else {
                                $classMetadata->associationMappings[$fieldName]['joinTable']['name']
                                    = $schema . strstr( $mappedTableName, '.' );
                            }
                        }
                    }
                }
            }
        }

        if ( $blank ) {
            if ( false !== strpos( $classMetadata->getTableName(), '.' ) ) {
                $classMetadata->setTableName( str_replace( '.', '', strstr( $classMetadata->getTableName(), '.' ) ) );
                $sequenceGeneratorDefinition['sequenceName']=str_replace( '.', '', strstr( $sequenceGeneratorDefinition['sequenceName'], '.' ) );
                $classMetadata->setSequenceGeneratorDefinition( $sequenceGeneratorDefinition );
            }
        } else {
            if ( false === strpos( $classMetadata->getTableName(), '.' ) ) {
                $classMetadata->setTableName( $prefix.$classMetadata->getTableName() );
                $sequenceGeneratorDefinition['sequenceName']=$prefix.$sequenceGeneratorDefinition['sequenceName'];
                $classMetadata->setSequenceGeneratorDefinition( $sequenceGeneratorDefinition );
                if ( $schema ) {
                    $classMetadata->setTableName( $schema . '.' . $classMetadata->getTableName() );
                    $sequenceGeneratorDefinition['sequenceName']=$schema.'.'.$sequenceGeneratorDefinition['sequenceName'];
                    $classMetadata->setSequenceGeneratorDefinition( $sequenceGeneratorDefinition );
                }
            } else {
                $classMetadata->setTableName( str_replace( '.', '.'.$prefix, $classMetadata->getTableName() ) );
                $sequenceGeneratorDefinition['sequenceName']=str_replace( '.', '.'.$prefix, $sequenceGeneratorDefinition['sequenceName'] );
                $classMetadata->setSequenceGeneratorDefinition( $sequenceGeneratorDefinition );
                if ( $schema ) {
                    $classMetadata->setTableName( $schema . strstr( $classMetadata->getTableName(), '.' ) );
                    $sequenceGeneratorDefinition['sequenceName']=$schema.strstr( $sequenceGeneratorDefinition['sequenceName'], '.' );
                    $classMetadata->setSequenceGeneratorDefinition( $sequenceGeneratorDefinition );
                }
            }
        }
    }
}
