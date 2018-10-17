<?php

namespace Splash\Connectors\FakerBundle\Services;

use Splash\Components\FieldsFactory;

/**
 * @abstract    Fake Nodes Fields Builder Service
 */
class FieldsBuilder
{

    /**
     * @abstract    Fields Types Counter
     * @var     array
     */
    private $counters = array();
    
    /**
     * @abstract    Splash Fields Factory
     * @var FieldsFactory
     */
    private $FieldsFactory = Null;

    /**
     * @abstract    Setup Spash Field Factory
     * 
     * @return self
     */   
    public function init(FieldsFactory $Factory)
    {
        //====================================================================//
        // Initialize Splash Field Factory Class
        $this->FieldsFactory    =   $Factory;            
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
        if ( isset($Options["Group"]) && is_scalar($Options["Group"]) ) {
            $this->FieldsFactory->group($Options["Group"]);
        }         
        if (in_array("Required", $Options) ) {
            $this->FieldsFactory->isRequired();
        } 
        if (in_array("Listed", $Options) ) {
            $this->FieldsFactory->isListed();
        } 
        if (in_array("Logged", $Options) ) {
            $this->FieldsFactory->isLogged();
        } 
        if (in_array("ReadOnly", $Options) ) {
            $this->FieldsFactory->ReadOnly();
        } 
        if (in_array("WriteOnly", $Options) ) {
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
        $Tag = md5($MetaType . IDSPLIT . FieldsFactory::META_URL);
        
        //==============================================================================
        //      Detect Meta Data Field Type  
        switch($MetaType) {
            //==============================================================================
            //      OPENOBJECT => Mongo ObjectId  
            case FieldsFactory::META_OBJECTID:
            //==============================================================================
            //      OPENOBJECT => Creation Date  
            case FieldsFactory::META_DATECREATED:
            //==============================================================================
            //      OPENOBJECT => Source Node Id  
            case FieldsFactory::META_OBJECTID:
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
                    ->MicroData(FieldsFactory::META_URL, $MetaType);
                        
        //==============================================================================
        // No Options   => Exit
        if ( is_null($Options)) {
            return $this;
        }      
        //==============================================================================
        // Setup Options
        if ( isset($Options["Group"]) && is_scalar($Options["Group"]) ) {
            $this->FieldsFactory->group($Options["Group"]);
        } 
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