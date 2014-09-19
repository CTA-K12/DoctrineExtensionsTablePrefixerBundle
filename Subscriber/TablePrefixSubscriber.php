<?php
namespace Mesd\DoctrineExtensions\TablePrefixerBundle\Subscriber;

use Doctrine\Common\EventSubscriber;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;

class TablePrefixSubscriber implements EventSubscriber
{
    /**
     * Array of options for each bundle
     * @var array
     */
    protected $bundles;

    /**
     * Flag pertaining to whether the defaults were set or not
     * @var boolean
     */
    protected $defaultsSet;

    /**
     * Array of default options
     * @var array
     */
    protected $defaults;


    /**
     * Consturctor
     *
     * @param ContainerInterface $container Reference to symfonys container
     */
    public function __construct(ContainerInterface $container)
    {
        if ($container->hasParameter('prefixed_bundles')) {
            $this->bundles = $container->getParameter('prefixed_bundles');
            $this->defaults = null;
            if (isset($this->bundles['default'])) {
                $this->defaults = $this->bundles['default'];
                if (!isset($this->defaults['flatten'])) {
                    $this->defaults['flatten'] = false;
                }
                $this->defaultsSet = true;
            } else {
                $this->defaultsSet = false;
            }
        }
    }


    /**
     * Get a list of the events this listener subscribes to
     *
     * @return array List of events subscribed to
     */
    public function getSubscribedEvents()
    {
        return array('loadClassMetadata' , 'prePersist');
    }


    /**
     * Action to call on the prepersist event
     *
     * @param  LifecycleEventArgs $eventArgs The event arguments
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getEntityManager()
            ->getClassMetadata(get_class($eventArgs->getEntity()));
        $this->prefix($classMetadata);
    }


    /**
     * Action to call on the load class metadata event
     *
     * @param  LoadClassMetadataEventArgs $eventArgs The event arguments
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();
        $this->prefix($classMetadata);
    }


    /**
     * Prefixes the class metadata with the requested schema name
     *
     * @param ClassMetadata $classMetadata The class metadata to modify
     */
    public function prefix(ClassMetadata $classMetadata)
    {
        if ($this->defaultsSet) {
            $blank = ('blank' == $this->defaults['schema']);
        } else {
            $blank = false;
        }

        $bundle = strstr($classMetadata->getName(), 'Bundle\\', true) . 'Bundle';
        $prefix = isset($this->bundles[$bundle]['prefix']) ? $this->bundles[$bundle]['prefix'] : null;
        $schema = isset($this->bundles[$bundle]['schema']) ? $this->bundles[$bundle]['schema'] : null;
        $flatten = isset($this->bundles[$bundle]['flatten']) ? $this->bundles[$bundle]['flatten'] : null;
        if ($classMetadata->isIdGeneratorSequence()) {
            $sequenceGeneratorDefinition = $classMetadata->sequenceGeneratorDefinition;
        }

        if ($this->defaults || !isset($this->bundles[$bundle])) {
            $prefix = (is_null($prefix) ? $this->defaults['prefix'] : $prefix);
            $schema = (is_null($schema) ? $this->defaults['schema'] : $schema);
            $flatten = (is_null($flatten) ? $this->defaults['flatten'] : $flatten);
        }

        foreach ($classMetadata->getAssociationMappings() as $fieldName => $mapping) {
            if ($mapping['type'] == \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY) {
                if ($classMetadata->associationMappings[$fieldName]['isOwningSide']) {
                    $mappedTableName = $classMetadata->associationMappings[$fieldName]['joinTable']['name'];
                    if ($blank) {
                        if (false !== strpos($classMetadata->getTableName(), '.')) {
                            $classMetadata->associationMappings[$fieldName]['joinTable']['name']
                                = str_replace('.', '', strstr($mappedTableName, '.'))
                            ;
                        }
                    } else {
                        $classMetadata->associationMappings[$fieldName]['joinTable']['name']
                            = $prefix . $mappedTableName;
                        if ($schema) {
                            if (((false === strpos($mappedTableName, '.')) && !$flatten)
                                || ((false === strpos($mappedTableName, '___'))
                                    && (false === strpos($mappedTableName, '.'))
                                    && $flatten)) {
                                if ($flatten) {
                                    $classMetadata->associationMappings[$fieldName]['joinTable']['name']
                                        = $schema . '___' . $mappedTableName ;
                                } else {
                                    $classMetadata->associationMappings[$fieldName]['joinTable']['name']
                                        = $schema . '.' . $mappedTableName ;
                                }
                            } else {
                                if ($flatten) {
                                    if (false === strpos($mappedTableName, '.')) {
                                        $classMetadata->associationMappings[$fieldName]['joinTable']['name']
                                            = $schema . strstr($mappedTableName, '___');
                                    } else {
                                        $classMetadata->associationMappings[$fieldName]['joinTable']['name']
                                            = $schema . str_replace('.', '___', strstr($mappedTableName, '.'));
                                    }
                                } else {
                                    $classMetadata->associationMappings[$fieldName]['joinTable']['name']
                                        = $schema . strstr($mappedTableName, '.');
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($blank) {
            if (false !== strpos($classMetadata->getTableName(), '.')) {
                $classMetadata->setTableName(str_replace('.', '', strstr($classMetadata->getTableName(), '.')));
                if ($classMetadata->isIdGeneratorSequence()) {
                    $sequenceGeneratorDefinition['sequenceName'] = str_replace('.', '', strstr($sequenceGeneratorDefinition['sequenceName'], '.'));
                    $classMetadata->setSequenceGeneratorDefinition($sequenceGeneratorDefinition);
                }
            }
        } else {
            if (((false === strpos($classMetadata->getTableName(), '.')) && !$flatten) 
                || ((false === strpos($classMetadata->getTableName(), '___'))
                    && (false === strpos($classMetadata->getTableName(), '.')) 
                    && $flatten)) {
                $classMetadata->setTableName($prefix.$classMetadata->getTableName());
                if ($schema) {
                    if ($flatten) {
                        $classMetadata->setTableName($schema . '___' . $classMetadata->getTableName());
                    } else {
                        $classMetadata->setTableName($schema . '.' . $classMetadata->getTableName());
                    }
                }
                if ($classMetadata->isIdGeneratorSequence()) {
                    $sequenceGeneratorDefinition['sequenceName'] = $prefix.$sequenceGeneratorDefinition['sequenceName'];
                    $classMetadata->setSequenceGeneratorDefinition($sequenceGeneratorDefinition);
                    if ($schema) {
                        $sequenceGeneratorDefinition['sequenceName'] = $schema.'.'.$sequenceGeneratorDefinition['sequenceName'];
                        $classMetadata->setSequenceGeneratorDefinition($sequenceGeneratorDefinition);
                    }
                }
            } else {
                if ($flatten) {
                    if (false === strpos($classMetadata->getTableName(), '.')) {
                        $classMetadata->setTableName(str_replace('___', '___' . $prefix, $classMetadata->getTableName()));
                    } else {
                        $classMetadata->setTableName(str_replace('.', '___' . $prefix, $classMetadata->getTableName()));
                    }
                } else {
                    $classMetadata->setTableName(str_replace('.', '.' . $prefix, $classMetadata->getTableName()));
                }
                if ($schema) {
                    if ($flatten) {
                        $classMetadata->setTableName($schema . strstr($classMetadata->getTableName(), '___'));
                    } else {
                        $classMetadata->setTableName($schema . strstr($classMetadata->getTableName(), '.'));
                    }
                }
                if ($classMetadata->isIdGeneratorSequence()) {
                    $sequenceGeneratorDefinition['sequenceName'] = str_replace('.', '.' . $prefix, $sequenceGeneratorDefinition['sequenceName']);
                    $classMetadata->setSequenceGeneratorDefinition($sequenceGeneratorDefinition);
                    if ($schema) {
                        $sequenceGeneratorDefinition['sequenceName'] = $schema . strstr($sequenceGeneratorDefinition['sequenceName'], '.');
                        $classMetadata->setSequenceGeneratorDefinition($sequenceGeneratorDefinition);
                    }
                }
            }
        }
    }
}
