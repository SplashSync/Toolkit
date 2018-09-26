<?php

namespace Nodes\FakerBundle\Services;

use OpenObject\WsSchemasBundle\Entity\WsSchema;
use OpenObject\CoreBundle\Document\OpenObjectFieldCore as Field;

use Splash\Components\FieldsFactory;

/**
 * @abstract    Fake Nodes Fields Builder Service
 */
class FieldsBuilderService 
{

    /*
     *  Fields Types Counter
     * @var array
     */
    private $counters = array();
    
    private $FieldsFactory = Null;

    /**
     * @abstract    Setup Spash Field Factory
     * 
     * @return self
     */   
    public function init()
    {
        //====================================================================//
        // Initialize Splash Field Factory Class
        $this->FieldsFactory    =   new FieldsFactory();            
        
        //====================================================================//
        // Clear Fields Counters
        $this->counters = array();
        
        return $this;
    }    
    
    /**
     * @abstract    Return Field Factory Data
     * 
     * @return array
     */   
    public function publish()
    {
        return $this->FieldsFactory->Publish();
    }    

    
//====================================================================//
//  COMMON FUNCTIONS
//====================================================================//

    /**
     * @abstract    Increment Field Type Counter
     * 
     * @return int  New Value
     */   
    public function count($Type)
    {
        if ( !isset($this->counters[$Type]) ) {
            $this->counters[$Type]  = 0;
        }
        $this->counters[$Type]++;
        return $this->counters[$Type];
    }    
    
    /**
     * @abstract    Add Field to FieldFactory
     * 
     * @param string                $FieldType     
     * @param array                 $Options     
     * 
     * @return self  
     */   
    public function add($FieldType, $Options = Null)
    {
        //==============================================================================
        // Init Parameters
        $Count  =   $this->count($FieldType);
        $Name   =   $FieldType . $Count;
        //==============================================================================
        // Add Field Core Infos
        $this->FieldsFactory->Create($FieldType)
                    ->Identifier($Name)
                    ->Name(strtoupper($Name))
                    ->Description("Fake Field - Type " . strtoupper($FieldType) . " Item " . $Count)
                    ->MicroData("http://fake.schema.org/" . $FieldType, $FieldType . $Count);
                        
        //==============================================================================
        // No Options   => Exit
        if ( is_null($Options)) {
            return $this;
        }
        
        //==============================================================================
        // Setup Options
        if ( isset($Options["Required"]) ) {
            $this->FieldsFactory->isRequired();
        } 
        if ( isset($Options["Listed"]) ) {
            $this->FieldsFactory->isListed();
        } 
        if ( isset($Options["Logged"]) ) {
            $this->FieldsFactory->isLogged();
        } 
        if ( isset($Options["ReadOnly"]) ) {
            $this->FieldsFactory->ReadOnly();
        } 
        if ( isset($Options["WriteOnly"]) ) {
            $this->FieldsFactory->WriteOnly();
        } 
        
        return $this;
    }     
    
    /**
     * @abstract    Add Meta Field to FieldFactory
     * 
     * @param string                $MetaType     
     * @param array                 $Options     
     * 
     * @return self  
     */   
    public function addMeta($MetaType, $Options = Null)
    {
        //==============================================================================
        // Init Parameters
        $Count  =   $this->count($MetaType);
        $Name   =   "m_" . $MetaType . $Count;

        //==============================================================================
        // Safety Check - Verify is Meta Type
        $Tag = md5($MetaType . IDSPLIT . WsSchema::META_URL);
        if ( !WsSchema::isMetaTag($Tag) ) {
            return $this;
        }
        
        //==============================================================================
        //      Detect Meta Data Field Type  
        switch($MetaType) {
            //==============================================================================
            //      OPENOBJECT => Mongo ObjectId  
            case WsSchema::META_OBJECTID:
            //==============================================================================
            //      OPENOBJECT => Creation Date  
            case WsSchema::META_DATECREATED:
            //==============================================================================
            //      OPENOBJECT => Source Node Id  
            case WsSchema::META_OBJECTID:
                $FieldType = SPL_T_VARCHAR;
                break;
            //==============================================================================
            //      UNKNOWN => Exit  
            default:
                return $this;
        }
        
        //==============================================================================
        // Add Field Core Infos
        $this->FieldsFactory->Create($FieldType)
                    ->Identifier($Name)
                    ->Name(strtoupper($Name))
                    ->Description("Fake Field - Meta Type " . strtoupper($MetaType) . " Item " . $Count)
                    ->MicroData(WsSchema::META_URL, $MetaType);
                        
        //==============================================================================
        // Setup Options
        if ( Field::isScalarType($FieldType)) {
            $this->FieldsFactory->isListed();
        }
        
        //==============================================================================
        // No Options   => Exit
        if ( is_null($Options)) {
            return $this;
        }
        
        //==============================================================================
        // Setup Options
        if ( isset($Options["Required"]) ) {
            $this->FieldsFactory->isRequired();
        } 
        if ( isset($Options["Listed"]) ) {
            $this->FieldsFactory->isListed();
        } 
        if ( isset($Options["Logged"]) ) {
            $this->FieldsFactory->isLogged();
        } 
        if ( isset($Options["ReadOnly"]) ) {
            $this->FieldsFactory->ReadOnly();
        } 
        if ( isset($Options["WriteOnly"]) ) {
            $this->FieldsFactory->WriteOnly();
        } 
        
        return $this;
    }     
    
    /**
     * @abstract    Compare Two Fields Definition Array
     * 
     * @param array     $A     
     * @param array     $B
     * 
     * @return self  
     */   
    public function compare($A, $B) {
        
        //==============================================================================
        // Compare Each Array Row
        foreach ($A as $key => $value) {
            //==============================================================================
            // Compare Simple Rows
            if (!is_array($value) && ($B[$key] != $value) ) {
                return False;
            } elseif (!is_array($value) ) {
                continue;
            }
            //==============================================================================
            // Compare Array Rows
            if (empty($value) && empty($B[$key]) ) {
                continue;
            }
            if ( $this->compare($value,$B[$key]) ) {
                continue;
            }
            return False;
        } 
        
        return True;
    }
            
            
}