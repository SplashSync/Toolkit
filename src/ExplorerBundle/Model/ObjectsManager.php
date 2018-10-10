<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\ExplorerBundle\Model;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\LockMode;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnitOfWork;
use Exporter\Source\DoctrineORMQuerySourceIterator;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Exception\LockException;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\Model\LockInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\DoctrineORMAdminBundle\Admin\FieldDescription;
use Sonata\DoctrineORMAdminBundle\Datagrid\OrderByToSelectWalker;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Bridge\Doctrine\RegistryInterface;


use Symfony\Component\Form\Exception\PropertyAccessDeniedException;



use ArrayObject;

use Splash\Core\SplashCore as Splash;
use Splash\Components\FieldsManager;

use Splash\Bundle\Services\ConnectorsManager;
//use Doctrine\ORM\Mapping\ClassMetadata;
use Splash\Bundle\Models\ConnectorInterface;


class ObjectsManager implements ModelManagerInterface, LockInterface
{
    /**
     * @var ConnectorsManager
     */
    private $Manager;    
    
    /**
     * @var string
     */
    private $connexion;
    
    /**
     * Current Splash Connector Service
     * @var ConnectorInterface
     */
    private $Connector;
        
    /**
     * @var string
     */
    private $ObjectType = null;
        
    const ID_SEPARATOR = '~';
    /**
     * @var RegistryInterface
     */
    protected $registry;

    /**
     * @var EntityManager[]
     */
    protected $cache = [];

    public function __construct(ConnectorsManager $manager)
    {
        $this->Manager = $manager;
    }

    /**
     * @abstract    Select Splash Bundle Connection to Use
     * @param   string   $ServerId
     * @return  $this
     */
    public function setConnection(string $ServerId) 
    {
        $this->connexion    =   $ServerId;
        $this->Connector    =   $this->Manager->get($ServerId);
        if (!$this->Connector) {
            throw new \RuntimeException('Unable to Identify linked Connector');
        }
        return $this;
    }    

//    /**
//     * @abstract    Select Splash Bundle Connection to Use
//     * @param   string   $ServerId
//     * @return  $this
//     */
//    public function setObjectType(string $ObjectType) 
//    {
//        $this->ObjectType    =   $ObjectType;
//        return $this;
//    }  
    
    /**
     * @abstract    Get Current Splash Connetor
     * @return      ConnectorInterface
     */
    public function getConnector() 
    {
        return $this->Connector;
    }
    
    /**
     * @abstract    Get Connetor Configuration
     * @return      array
     */
    public function getConfiguration() 
    {
        return $this->Manager->getServerConfiguration($this->connexion);
    }    
    
    /**
     * @abstract    Fetch Connector Available Objects Types
     * 
     * @return     ArrayObject|bool
     */    
    public function getObjects()
    {
        //====================================================================//
        // Read Objects Type List        
        return $this->getConnector()->getAvailableObjects(
            $this->getConfiguration()
        );
    }
    
    /**
     * @abstract    Fetch Connector Available Objects List 
     * 
     * @return     ArrayObject|bool
     */    
    public function getObjectsDefinition()
    {
        //====================================================================//
        // Read Objects Type List        
        $ObjectTypes =  $this->getConnector()->getAvailableObjects(
            $this->getConfiguration()
        );
        //====================================================================//
        // Read Description of All Objects        
        $Objects    =   array();
        foreach ($ObjectTypes as $ObjectType) {
            $Objects[$ObjectType]   =   $this->getConnector()->getObjectDescription(
                $this->getConfiguration(),
                $ObjectType
            );
        }
        return $Objects;
    }
    
    /**
     * @param string $class
     *
     * @return ClassMetadata
     */
    public function getObjectFields()
    {
        return $this->getConnector()->getObjectFields(
                $this->getConfiguration(),
                $this->ObjectType
            );
    }
    
    public function getObject($Ids, $Fields )
    {
        return $this->getConnector()->getObject(
                $this->getConfiguration(),
                $this->ObjectType,
                $Ids,
                $Fields
            );
    }    
    
    public function setObject($Id, $Data )
    {
        return $this->getConnector()->setObject(
                $this->getConfiguration(),
                $this->ObjectType,
                $Id,
                $Data
            );
    }        
    
    /**
     * @param string $class
     *
     * @return ClassMetadata
     */
    public function getMetadata($class)
    {
        return new ClassMetadata("ArrayObject");
    }

//    /**
//     * Returns the model's metadata holding the fully qualified property, and the last
//     * property name.
//     *
//     * @param string $baseClass        The base class of the model holding the fully qualified property
//     * @param string $propertyFullName The name of the fully qualified property (dot ('.') separated
//     *                                 property string)
//     *
//     * @return array(
//     *                \Doctrine\ORM\Mapping\ClassMetadata $parentMetadata,
//     *                string $lastPropertyName,
//     *                array $parentAssociationMappings
//     *                )
//     */
//    public function getParentMetadataForProperty($baseClass, $propertyFullName)
//    {
//        $nameElements = explode('.', $propertyFullName);
//        $lastPropertyName = array_pop($nameElements);
//        $class = $baseClass;
//        $parentAssociationMappings = [];
//
//        foreach ($nameElements as $nameElement) {
//            $metadata = $this->getMetadata($class);
//
//            if (isset($metadata->associationMappings[$nameElement])) {
//                $parentAssociationMappings[] = $metadata->associationMappings[$nameElement];
//                $class = $metadata->getAssociationTargetClass($nameElement);
//
//                continue;
//            }
//
//            break;
//        }
//
//        $properties = array_slice($nameElements, count($parentAssociationMappings));
//        $properties[] = $lastPropertyName;
//
//        return [$this->getMetadata($class), implode('.', $properties), $parentAssociationMappings];
//    }

    /**
     * @param string $class
     * @return bool
     */
    public function hasMetadata($class)
    {
        return false;
    }

    public function getNewFieldDescriptionInstance($class, $name, array $options = [])
    {
        if (!is_string($name)) {
            throw new \RuntimeException('The name argument must be a string');
        }
        $fieldDescription = new FieldDescription();
        $fieldDescription->setName($name);
        $fieldDescription->setOptions($options);
        return $fieldDescription;
        
        if (!isset($options['route']['name'])) {
            $options['route']['name'] = 'edit';
        }

        if (!isset($options['route']['parameters'])) {
            $options['route']['parameters'] = [];
        }

        list($metadata, $propertyName, $parentAssociationMappings) = $this->getParentMetadataForProperty($class, $name);

        $fieldDescription = new FieldDescription();
        $fieldDescription->setName($name);
        $fieldDescription->setOptions($options);
        $fieldDescription->setParentAssociationMappings($parentAssociationMappings);

        if (isset($metadata->associationMappings[$propertyName])) {
            $fieldDescription->setAssociationMapping($metadata->associationMappings[$propertyName]);
        }

        if (isset($metadata->fieldMappings[$propertyName])) {
            $fieldDescription->setFieldMapping($metadata->fieldMappings[$propertyName]);
        }

        return $fieldDescription;
    }

    public function create($object)
    {       
        unset($object->id);
        try {
            //====================================================================//
            // Write Object Data      
            $NewId  =   $this->setObject(null, $object->getArrayCopy());   
        } catch (\PDOException $e) {
            throw new ModelManagerException(
                sprintf('Failed to create object: %s', ClassUtils::getClass($object)),
                $e->getCode(),
                $e
            );
        } catch (DBALException $e) {
            throw new ModelManagerException(
                sprintf('Failed to create object: %s', ClassUtils::getClass($object)),
                $e->getCode(),
                $e
            );
        }
        //====================================================================//
        // Catch Splash Errors      
        if (!empty(Splash::log()->err)) {
            throw new ModelManagerException(
                sprintf('Failed to create object: %s', PHP_EOL . implode(PHP_EOL, Splash::log()->err))
            );
        }        
        
        $object->id =   $NewId;
    }

    public function update($object)
    {
        
dump($object);

        //====================================================================//
        // Safety Check - Verify Object has Id      
        if (empty($object->id)) {
            return;
        }
        //====================================================================//
        // Do Object Update     
        try {
            //====================================================================//
            // Write Object Data      
            $this->setObject($object->id, $object->getArrayCopy());        
        } catch (\PDOException $e) {
            throw new ModelManagerException(
                sprintf('Failed to update object: %s', ClassUtils::getClass($object)),
                $e->getCode(),
                $e
            );
        } catch (DBALException $e) {
            throw new ModelManagerException(
                sprintf('Failed to update object: %s', ClassUtils::getClass($object)),
                $e->getCode(),
                $e
            );
        }
        
        //====================================================================//
        // Catch Splash Errors      
        if (!empty(Splash::log()->err)) {
            throw new ModelManagerException(
                sprintf('Failed to update object: %s', PHP_EOL . implode(PHP_EOL, Splash::log()->err))
            );
        }
    }

    public function delete($object)
    {
        try {
            $entityManager = $this->getEntityManager($object);
            $entityManager->remove($object);
            $entityManager->flush();
        } catch (\PDOException $e) {
            throw new ModelManagerException(
                sprintf('Failed to delete object: %s', ClassUtils::getClass($object)),
                $e->getCode(),
                $e
            );
        } catch (DBALException $e) {
            throw new ModelManagerException(
                sprintf('Failed to delete object: %s', ClassUtils::getClass($object)),
                $e->getCode(),
                $e
            );
        }
    }

    public function getLockVersion($object)
    {
        $metadata = $this->getMetadata(ClassUtils::getClass($object));

        if (!$metadata->isVersioned) {
            return;
        }

        return $metadata->reflFields[$metadata->versionField]->getValue($object);
    }

    public function lock($object, $expectedVersion)
    {
//        $metadata = $this->getMetadata(ClassUtils::getClass($object));
//
//        if (!$metadata->isVersioned) {
//            return;
//        }
//
//        try {
//            $entityManager = $this->getEntityManager($object);
//            $entityManager->lock($object, LockMode::OPTIMISTIC, $expectedVersion);
//        } catch (OptimisticLockException $e) {
//            throw new LockException($e->getMessage(), $e->getCode(), $e);
//        }
    }

    public function find($class, $Id)
    {
        if (!isset($Id)) {
            return;
        }
        //====================================================================//
        // Prepare Writable Fields List
        $Fields = FieldsManager::reduceFieldList(
                $this->getObjectFields($this->ObjectType), 
                true, 
                true
            );
        
// dump($Fields); 
 dump($this->getObject($Id, $Fields));
        //====================================================================//
        // Read Object Data      
        return new ArrayObject($this->getObject($Id, $Fields), ArrayObject::ARRAY_AS_PROPS);
    }

    public function findBy($class, array $criteria = [])
    {
        return $this->getConnector()->object($class)->objectsList();
        return $this->getEntityManager($class)->getRepository($class)->findBy($criteria);
    }

    public function findOneBy($class, array $criteria = [])
    {
        return $this->getEntityManager($class)->getRepository($class)->findOneBy($criteria);
    }

    /**
     * @param string $class
     *
     * @return EntityManager
     */
    public function getEntityManager($class)
    {
        return $this;
        if (is_object($class)) {
            $class = get_class($class);
        }

        if (!isset($this->cache[$class])) {
            $em = $this->registry->getManagerForClass($class);

            if (!$em) {
                throw new \RuntimeException(sprintf('No entity manager defined for class %s', $class));
            }

            $this->cache[$class] = $em;
        }

        return $this->cache[$class];
    }

    public function getParentFieldDescription($parentAssociationMapping, $class)
    {
        $fieldName = $parentAssociationMapping['fieldName'];

        $metadata = $this->getMetadata($class);

        $associatingMapping = $metadata->associationMappings[$parentAssociationMapping];

        $fieldDescription = $this->getNewFieldDescriptionInstance($class, $fieldName);
        $fieldDescription->setName($parentAssociationMapping);
        $fieldDescription->setAssociationMapping($associatingMapping);

        return $fieldDescription;
    }

    public function createQuery($class, $alias = 'o')
    {
        return new ProxyQuery();
        $repository = $this->getEntityManager($class)->getRepository($class);

        return new ProxyQuery($repository->createQueryBuilder($alias));
    }

    public function executeQuery($query)
    {
        if ($query instanceof QueryBuilder) {
            return $query->getQuery()->execute();
        }

        return $query->execute();
    }

    public function getModelIdentifier($class)
    {
        return ["id"];
        return $this->getMetadata($class)->identifier;
    }

    public function getIdentifierValues($entity)
    {
        // Fix code has an impact on performance, so disable it ...
        //$entityManager = $this->getEntityManager($entity);
        //if (!$entityManager->getUnitOfWork()->isInIdentityMap($entity)) {
        //    throw new \RuntimeException('Entities passed to the choice field must be managed');
        //}

        $class = ClassUtils::getClass($entity);
        $metadata = $this->getMetadata($class);
        $platform = $this->getEntityManager($class)->getConnection()->getDatabasePlatform();

        $identifiers = [];

        foreach ($metadata->getIdentifierValues($entity) as $name => $value) {
            if (!is_object($value)) {
                $identifiers[] = $value;

                continue;
            }

            $fieldType = $metadata->getTypeOfField($name);
            $type = $fieldType && Type::hasType($fieldType) ? Type::getType($fieldType) : null;
            if ($type) {
                $identifiers[] = $type->convertToDatabaseValue($value, $platform);

                continue;
            }

            $metadata = $this->getMetadata(ClassUtils::getClass($value));

            foreach ($metadata->getIdentifierValues($value) as $value) {
                $identifiers[] = $value;
            }
        }

        return $identifiers;
    }

    public function getIdentifierFieldNames($class)
    {
        return $this->getMetadata($class)->getIdentifierFieldNames();
    }

    public function getNormalizedIdentifier($entity)
    {
        return $entity["id"];
        
        if (is_scalar($entity)) {
            throw new \RuntimeException('Invalid argument, object or null required');
        }

        if (!$entity) {
            return;
        }

        if (in_array($this->getEntityManager($entity)->getUnitOfWork()->getEntityState($entity), [
            UnitOfWork::STATE_NEW,
            UnitOfWork::STATE_REMOVED,
        ], true)) {
            return;
        }

        $values = $this->getIdentifierValues($entity);

        if (0 === count($values)) {
            return;
        }

        return implode(self::ID_SEPARATOR, $values);
    }

    /**
     * {@inheritdoc}
     *
     * The ORM implementation does nothing special but you still should use
     * this method when using the id in a URL to allow for future improvements.
     */
    public function getUrlsafeIdentifier($entity)
    {
        return $this->getNormalizedIdentifier($entity);
    }

    public function addIdentifiersToQuery($class, ProxyQueryInterface $queryProxy, array $idx)
    {
        $fieldNames = $this->getIdentifierFieldNames($class);
        $qb = $queryProxy->getQueryBuilder();

        $prefix = uniqid();
        $sqls = [];
        foreach ($idx as $pos => $id) {
            $ids = explode(self::ID_SEPARATOR, $id);

            $ands = [];
            foreach ($fieldNames as $posName => $name) {
                $parameterName = sprintf('field_%s_%s_%d', $prefix, $name, $pos);
                $ands[] = sprintf('%s.%s = :%s', current($qb->getRootAliases()), $name, $parameterName);
                $qb->setParameter($parameterName, $ids[$posName]);
            }

            $sqls[] = implode(' AND ', $ands);
        }

        $qb->andWhere(sprintf('( %s )', implode(' OR ', $sqls)));
    }

    public function batchDelete($class, ProxyQueryInterface $queryProxy)
    {
        $queryProxy->select('DISTINCT '.current($queryProxy->getRootAliases()));

        try {
            $entityManager = $this->getEntityManager($class);

            $i = 0;
            foreach ($queryProxy->getQuery()->iterate() as $pos => $object) {
                $entityManager->remove($object[0]);

                if (0 == (++$i % 20)) {
                    $entityManager->flush();
                    $entityManager->clear();
                }
            }

            $entityManager->flush();
            $entityManager->clear();
        } catch (\PDOException $e) {
            throw new ModelManagerException('', 0, $e);
        } catch (DBALException $e) {
            throw new ModelManagerException('', 0, $e);
        }
    }

    public function getDataSourceIterator(DatagridInterface $datagrid, array $fields, $firstResult = null, $maxResult = null)
    {
        $datagrid->buildPager();
        $query = $datagrid->getQuery();

        $query->select('DISTINCT '.current($query->getRootAliases()));
        $query->setFirstResult($firstResult);
        $query->setMaxResults($maxResult);

        if ($query instanceof ProxyQueryInterface) {
            $sortBy = $query->getSortBy();

            if (!empty($sortBy)) {
                $query->addOrderBy($sortBy, $query->getSortOrder());
                $query = $query->getQuery();
                $query->setHint(Query::HINT_CUSTOM_TREE_WALKERS, [OrderByToSelectWalker::class]);
            } else {
                $query = $query->getQuery();
            }
        }

        return new DoctrineORMQuerySourceIterator($query, $fields);
    }

    public function getExportFields($class)
    {
        $metadata = $this->getEntityManager($class)->getClassMetadata($class);

        return $metadata->getFieldNames();
    }

    public function getModelInstance($class)
    {
        $r = new \ReflectionClass($class);
        if ($r->isAbstract()) {
            throw new \RuntimeException(sprintf('Cannot initialize abstract class: %s', $class));
        }

        return new $class();
    }

    public function getSortParameters(FieldDescriptionInterface $fieldDescription, DatagridInterface $datagrid)
    {
        $values = $datagrid->getValues();

        if ($fieldDescription->getName() == $values['_sort_by']->getName() || $values['_sort_by']->getName() === $fieldDescription->getOption('sortable')) {
            if ('ASC' == $values['_sort_order']) {
                $values['_sort_order'] = 'DESC';
            } else {
                $values['_sort_order'] = 'ASC';
            }
        } else {
            $values['_sort_order'] = 'ASC';
        }

        $values['_sort_by'] = is_string($fieldDescription->getOption('sortable')) ? $fieldDescription->getOption('sortable') : $fieldDescription->getName();

        return ['filter' => $values];
    }

    public function getPaginationParameters(DatagridInterface $datagrid, $page)
    {
        $values = $datagrid->getValues();

        $values['_sort_by'] = $values['_sort_by']->getName();
        $values['_page'] = $page;

        return ['filter' => $values];
    }

    public function getDefaultSortValues($class)
    {
        return [
            '_sort_order' => 'ASC',
            '_sort_by' => implode(',', $this->getModelIdentifier($class)),
            '_page' => 1,
            '_per_page' => 25,
        ];
    }

    public function modelTransform($class, $instance)
    {
        return $instance;
    }

    public function modelReverseTransform($class, array $array = [])
    {
        $instance = $this->getModelInstance($class);
        $metadata = $this->getMetadata($class);

        $reflClass = $metadata->reflClass;
        foreach ($array as $name => $value) {
            $reflection_property = false;
            // property or association ?
            if (array_key_exists($name, $metadata->fieldMappings)) {
                $property = $metadata->fieldMappings[$name]['fieldName'];
                $reflection_property = $metadata->reflFields[$name];
            } elseif (array_key_exists($name, $metadata->associationMappings)) {
                $property = $metadata->associationMappings[$name]['fieldName'];
            } else {
                $property = $name;
            }

            $setter = 'set'.$this->camelize($name);

            if ($reflClass->hasMethod($setter)) {
                if (!$reflClass->getMethod($setter)->isPublic()) {
                    throw new PropertyAccessDeniedException(sprintf(
                        'Method "%s()" is not public in class "%s"',
                        $setter,
                        $reflClass->getName()
                    ));
                }

                $instance->$setter($value);
            } elseif ($reflClass->hasMethod('__set')) {
                // needed to support magic method __set
                $instance->$property = $value;
            } elseif ($reflClass->hasProperty($property)) {
                if (!$reflClass->getProperty($property)->isPublic()) {
                    throw new PropertyAccessDeniedException(sprintf(
                        'Property "%s" is not public in class "%s". Maybe you should create the method "set%s()"?',
                            $property,
                            $reflClass->getName(),
                            ucfirst($property)
                    ));
                }

                $instance->$property = $value;
            } elseif ($reflection_property) {
                $reflection_property->setValue($instance, $value);
            }
        }

        return $instance;
    }

    public function getModelCollectionInstance($class)
    {
        return new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function collectionClear(&$collection)
    {
        return $collection->clear();
    }

    public function collectionHasElement(&$collection, &$element)
    {
        return $collection->contains($element);
    }

    public function collectionAddElement(&$collection, &$element)
    {
        return $collection->add($element);
    }

    public function collectionRemoveElement(&$collection, &$element)
    {
        return $collection->removeElement($element);
    }

    /**
     * method taken from Symfony\Component\PropertyAccess\PropertyAccessor.
     *
     * @param string $property
     *
     * @return mixed
     */
    protected function camelize($property)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $property)));
    }
    
    
    /**
     *   @abstract   Redure a Fields List to an Array of Field Ids
     *
     *   @param      array      $FieldsList     Object Field List
     *   @param      bool       $isRead         Filter non Readable Fields
     *   @param      bool       $isWrite        Filter non Writable Fields
     *
     *   @return     array
     */
    public static function reduceFieldList($FieldsList, $isRead = false, $isWrite = false)
    {
        $Result =   array();
       
        foreach ($FieldsList as $Field) {
            //==============================================================================
            //      Filter Non-Readable Fields
            if ($isRead && !$Field->read) {
                continue;
            }
            //==============================================================================
            //      Filter Non-Writable Fields
            if ($isWrite && !$Field->write) {
                continue;
            }
            $Result[] = $Field->id;
        }
            
        return $Result;
    } 
    
    /**
     * @abstract    Setup Current Splash Object Type
     * @param   string  $ObjectType
     * @return  $this
     */
    public function setObjectType(string $ObjectType)
    {
        $this->ObjectType   =   $ObjectType;
        return $this;
    } 
    
     
}
