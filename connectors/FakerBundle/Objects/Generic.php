<?php

/**
 * This file is part of SplashSync Project.
 *
 * Copyright (C) Splash Sync <www.splashsync.com>
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * @author Bernard Paquier <contact@splashsync.com>
 */

namespace Splash\Connectors\FakerBundle\Objects;

use ArrayObject;

use Doctrine\ORM\EntityManagerInterface;

use Splash\Client\Splash;
use Splash\Components\FieldsFactory;

use Splash\Models\AbstractObject;
use Splash\Models\Objects\IntelParserTrait;
use Splash\Models\Objects\SimpleFieldsTrait;

use Splash\Connectors\FakerBundle\Entity\FakeObject;
use Splash\Connectors\FakerBundle\Services\FieldsBuilder;

/**
 * Description of Generic
 *
 * @author nanard33
 */
class Generic extends AbstractObject {
    
    // Splash Php Core Traits
    use IntelParserTrait;
    use SimpleFieldsTrait;

    //====================================================================//
    // Object Definition Parameters
    //====================================================================//
    
    /**
     *  Object Disable Flag. Uncomment thius line to Override this flag and disable Object.
     */
//    protected static    $DISABLED        =  True;
    
    /**
     *  Object Description (Translated by Module)
     */
    protected static $DESCRIPTION     =  "Faker Object";
    
    /**
     *  Object Icon (FontAwesome or Glyph ico tag)
     */
    protected static $ICO     =  "fa fa-";
    
    //====================================================================//
    // Private variables
    //====================================================================//
    
    /**
     *  @var string
     */
    private $type;
    
    /**
     * @var string
     */
    private $name;
         
    /**
     * @var string
     */
    private $format;
    
    /**
     * @abstract Doctrine Entity Manager
     * @var EntityManagerInterface
     */
    private $_em;

    /**
     * @var FieldsBuilder
     */
    private $fieldBuilder;

    //====================================================================//
    // Service Constructor
    //====================================================================//
        
    public function __construct(FieldsBuilder $FieldsBuilder, EntityManagerInterface $EntityManager) {
        //====================================================================//
        // Link to Fake Fields Builder Services
        $this->fieldBuilder =   $FieldsBuilder;
        //====================================================================//
        // Link to Doctrine Entity Manager Services
        $this->_em = $EntityManager;        
    }

    /**
     * @param string $type
     * @param string $name
     * @param string $format
     */
    public function setConfiguration(string $type, string $name, string $format)
    {
        $this->type     =   $type;
        $this->name     =   $name;
        $this->format   =   $format;       
    }    
  
    /**
     * @abstract     Build Core Fields using FieldFactory
     */
    public function buildCoreFields()
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__,__FUNCTION__);   
        //====================================================================//
        // Generate Fake Fields        
        $this->generateFieldsSet($this->format);
    }    
    
    /**
     *  @abstract     Read requested Field
     *
     *  @param        string    $Key                    Input List Key
     *  @param        string    $FieldName              Field Identifier / Name
     *
     *  @return         none
     */
    public function getCoreFields($Key, $FieldName)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__,__FUNCTION__);   
        //====================================================================//
        // Read Data
        $this->getSimple($FieldName);
        unset($this->In[$Key]);
    } 
    
    /**
     *  @abstract     Write Given Fields
     *
     *  @param        string    $FieldName              Field Identifier / Name
     *  @param        mixed     $Data                   Field Data
     *
     *  @return         none
     */
    protected function setCoreFields($FieldName, $Data)
    {    
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__,__FUNCTION__);   
        //====================================================================//
        // Read Data
        $this->setSimple($FieldName, $Data);
    } 
    
    /**
     * {@inheritdoc}
     */    
    public function ObjectsList($filter=NULL,$params=NULL)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__,__FUNCTION__);          

        $Response = [];
        $Repo   =   $this->_em->getRepository('SplashFakerBundle:FakeObject');        
        
        //====================================================================//
        // Prepare List Filters List
        $Search     =   array(
            "type"      => $this->type,
                );
        if ( !empty($filter) ) {
            $Search["identifier"] = $filter;
        }
        //====================================================================//
        // Load Objects List
        $Data = $Repo->findBy($Search, array(), $params["max"] , $params["offset"] );
            
        //====================================================================//
        // Load Object Fields
        $Fields =   $this->fields();

        //====================================================================//
        // Parse Data on Result Array
        /** @var FakeObject $Object */
        foreach ($Data as $Object) {
            
            $ObjectData =   array(
                "id"    =>   $Object->getIdentifier()
                    );
            
            foreach ($Fields as $Field) {
                if ( $Field["inlist"] ) {
                    $ObjectData[$Field["id"]] =   $Object->getData($Field["id"]);
                }
            }
            
            $Response[] = $ObjectData;  
        }
            
        //====================================================================//
        // Parse Meta Infos on Result Array
        $Response["meta"] =  array(
            "total"   => $Repo->getTypeCount($this->type, $filter), 
            "current" => count($Data)
            );
        
        //====================================================================//
        // Return result
        return $Response;
    }
    
    /**
     * @abstract    Load Request Object
     * @param       string  $Id               Object id
     * @return      mixed
     */
    public function load($Id)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__,__FUNCTION__);          
        //====================================================================//
        // Search in Repository
        $Entity   =   $this->_em
                ->getRepository('SplashFakerBundle:FakeObject')
                ->findOneBy(array(
                    "type"      => $this->type,
                    "identifier"=> $Id
                ));         
        //====================================================================//
        // Check Object Entity was Found
        if (!$Entity) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Unable to load " . $this->name . " (" . $Id . ")."
            );
        }
        return new ArrayObject($Entity->getData(), ArrayObject::ARRAY_AS_PROPS);
    }
    
    /**
     * @abstract    Create Request Object
     *
     * @param       array   $List         Given Object Data
     *
     * @return      object     New Object
     */
    public function create()
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);    
        
        //====================================================================//
        // Create New Entity
        $Entity =   new FakeObject();
        $Entity->setType($this->type);
        
        //====================================================================//
        // Save
        $this->_em->persist($Entity);
        $Entity->setIdentifier($Entity->getId());
        $this->_em->flush();        

        return $Entity;
    }
    
    /**
     * @abstract    Update Request Object
     *
     * @param       array   $Needed         Is This Update Needed
     *
     * @return      string      Object Id
     */
    public function update($Needed)
    {
        //====================================================================//
        // Save
        if ($Needed) {
            $this->_em->flush();      
        }
    }
    
    /**
     * {@inheritdoc}
     */    
    public function delete($id = null)
    {
        $Entity = $this->load($id);
        if($Entity) {
            //====================================================================//
            // Delete
            $this->_em->remove($Entity);
            $this->_em->flush(); 
        }
        return true;
    }        
    
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @abstract    Generate Fake Node Field 
     * 
     * @param string    $FieldSetType   
     * 
     * @return array
     */   
    public function generateFieldsSet($FieldSetType)
    {
        //====================================================================//
        // Load Field Builder Service
        $this->fieldBuilder->Init( $this->fieldsFactory() );        
        //==============================================================================
        // Populate Fields Array
        switch($FieldSetType) {
            case "short":
                
                //==============================================================================
                // Short Objects Fields Definition
                $this->fieldBuilder->add(SPL_T_VARCHAR, array("Listed"));
                $this->fieldBuilder->add(SPL_T_BOOL,    array("Listed"));
                $this->fieldBuilder->add(SPL_T_INT,     array("Listed"));
                $this->fieldBuilder->add(SPL_T_BOOL,    array());
                $this->fieldBuilder->add(SPL_T_INT,     array());
                $this->fieldBuilder->add(SPL_T_VARCHAR, array());
                $this->fieldBuilder->add(SPL_T_EMAIL,   array("Listed"));
                $this->fieldBuilder->add(SPL_T_PHONE,   array());
                
                break;
            
            
            case "simple":
                
                //==============================================================================
                // Simple Objects Fields Definition
                $this->fieldBuilder->add(SPL_T_VARCHAR,array());
                $this->fieldBuilder->add(SPL_T_VARCHAR,array());
                $this->fieldBuilder->add(SPL_T_BOOL,array());
                $this->fieldBuilder->add(SPL_T_INT,array());
                $this->fieldBuilder->add(SPL_T_DOUBLE,array());
                $this->fieldBuilder->add(SPL_T_DATE,array());
                $this->fieldBuilder->add(SPL_T_DATETIME,array());
                $this->fieldBuilder->add(SPL_T_CURRENCY,array());
                $this->fieldBuilder->add(SPL_T_LANG,array());
                $this->fieldBuilder->add(SPL_T_STATE,array());
                $this->fieldBuilder->add(SPL_T_COUNTRY,array());
                $this->fieldBuilder->add(SPL_T_EMAIL,array());
                $this->fieldBuilder->add(SPL_T_URL,array());                
                $this->fieldBuilder->add(SPL_T_PHONE,array());
                $this->fieldBuilder->add(SPL_T_PRICE,array());
                
                break;
            
            case "list":
                
                $this->fieldBuilder->add(SPL_T_VARCHAR,array());
                $this->fieldBuilder->add(SPL_T_BOOL,array());
                $this->fieldBuilder->add(SPL_T_INT,array());
                
                //==============================================================================
                // Simple List Objects Fields Definition
                $this->fieldBuilder->add(SPL_T_BOOL        . LISTSPLIT . SPL_T_LIST,array());
                $this->fieldBuilder->add(SPL_T_INT         . LISTSPLIT . SPL_T_LIST,array());
                $this->fieldBuilder->add(SPL_T_DOUBLE      . LISTSPLIT . SPL_T_LIST,array());
                $this->fieldBuilder->add(SPL_T_VARCHAR     . LISTSPLIT . SPL_T_LIST,array());
                $this->fieldBuilder->add(SPL_T_TEXT        . LISTSPLIT . SPL_T_LIST,array());
                $this->fieldBuilder->add(SPL_T_EMAIL       . LISTSPLIT . SPL_T_LIST,array());
                $this->fieldBuilder->add(SPL_T_PHONE       . LISTSPLIT . SPL_T_LIST,array());
                $this->fieldBuilder->add(SPL_T_DATE        . LISTSPLIT . SPL_T_LIST,array());
                $this->fieldBuilder->add(SPL_T_DATETIME    . LISTSPLIT . SPL_T_LIST,array());
                $this->fieldBuilder->add(SPL_T_LANG        . LISTSPLIT . SPL_T_LIST,array());
                $this->fieldBuilder->add(SPL_T_COUNTRY     . LISTSPLIT . SPL_T_LIST,array());
                $this->fieldBuilder->add(SPL_T_STATE       . LISTSPLIT . SPL_T_LIST,array());
                $this->fieldBuilder->add(SPL_T_URL         . LISTSPLIT . SPL_T_LIST,array());
                $this->fieldBuilder->add(SPL_T_MVARCHAR    . LISTSPLIT . SPL_T_LIST,array());
                $this->fieldBuilder->add(SPL_T_MTEXT       . LISTSPLIT . SPL_T_LIST,array());
                $this->fieldBuilder->add(SPL_T_PRICE       . LISTSPLIT . SPL_T_LIST,array()); 
                
                break;

            case "image":
                
                $this->fieldBuilder->add(SPL_T_VARCHAR,array());
                $this->fieldBuilder->add(SPL_T_BOOL,array());
                $this->fieldBuilder->add(SPL_T_INT,array());
               
                //==============================================================================
                // Simple but with Image Fields Definition
                $this->fieldBuilder->add(SPL_T_IMG,array());
                
                break;             
            
            case "file":
                
                $this->fieldBuilder->add(SPL_T_VARCHAR,array());
                $this->fieldBuilder->add(SPL_T_BOOL,array());
                $this->fieldBuilder->add(SPL_T_INT,array());
               
                //==============================================================================
                // Simple but with File Fields Definition
                $this->fieldBuilder->add(SPL_T_FILE,array());
                
                break;             
            
        }
        
        //==============================================================================
        // Short Objects Meta Fields Definition
        $this->fieldBuilder->addMeta(FieldsFactory::META_OBJECTID);
        
    }   
    
}
