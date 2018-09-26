<?php
/*
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
 */

namespace   Splash\Local;

use Doctrine\ORM\EntityManager;

use Splash\Client\Splash;
use Splash\Models\AbstractObject;

use Nodes\FakerBundle\Entity\FakeNode;
use Nodes\FakerBundle\Entity\FakeObject;


/**
 * @abstract    Local Overiding Objects Manager for Splash Nodes Faker
 * @author      B. Paquier <contact@splashsync.com>
 */

class LocalObject extends AbstractObject
{

    /**
     *  @var string Object Type Name
     */
    private $type;
    
    /**
     *  Doctrine Entity Manager
     * @var EntityManager
     */
    private $_em;
    
    /**
     *  @var FakeNode
     */
    private $fake   = Null;
    
    private $In     = null;
    
    //====================================================================//
    // Object Definition Parameters	
    //====================================================================//
    
    /**
     *  Object Disable Flag. Uncomment thius line to Override this flag and disable Object.
     */
//    protected static    $DISABLED        =  True;
    
    /**
     *  Object Name (Translated by Module)
     */
    protected static    $NAME            =  "Fake Object";
    
    /**
     *  Object Description (Translated by Module) 
     */
    protected static    $DESCRIPTION     =  "Splash NodesFakerBunlde Generic Object";    
    
    /**
     *  Object Icon (FontAwesome or Glyph ico tag) 
     */
    protected static    $ICO             =  "fa fa-magic";
    
    //====================================================================//
    // General Class Variables	
    //====================================================================//

    //====================================================================//
    // Class Constructor
    //====================================================================//
        
    /**
     *      @abstract       Class Constructor (Used only if localy necessary)
     *      @return         int                     0 if KO, >0 if OK
     */
    function __construct(FakeNode $Faker, EntityManager $EntityManager, $ObjectType) 
    {
        //====================================================================//
        // Store Object Type
        $this->type     =   $ObjectType;
        
        // Link to Fake Node Entity
        $this->fake     =   $Faker;
        
        //====================================================================//
        // Link to Doctrine Entity Manager Services
        $this->_em = $EntityManager;
        
        return True;
    }    
    
    //====================================================================//
    // Class Main Functions
    //====================================================================//
    
    /**
    *   @abstract     Return List Of available data for Customer
    *   @return       array   $data             List of all customers available data
    *                                           All data must match with OSWS Data Types
    *                                           Use OsWs_Data::Define to create data instances
    */
    public function Fields()
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__,__FUNCTION__);             
        //====================================================================//
        // Publish Fields
        return $this->fake->getObjectFields($this->type);
    }
    
    /**
    *   @abstract     Return List Of Customer with required filters
     * 
    *   @param        string  $filter                   Filters/Search String for Contact List. 
    *   @param        array   $params                   Search parameters for result List. 
    *                         $params["max"]            Maximum Number of results 
    *                         $params["offset"]         List Start Offset 
    *                         $params["sortfield"]      Field name for sort list (Available fields listed below)    
    *                         $params["sortorder"]      List Order Constraign (Default = ASC)    
     * 
    *   @return       array   $data                     List of all customers main data
    *                         $data["meta"]["total"]     ==> Total Number of results
    *                         $data["meta"]["current"]   ==> Total Number of results
    */
    public function ObjectsList($filter=NULL,$params=NULL)
    {
        Splash::log()->deb("MsgLocalFuncTrace",__CLASS__,__FUNCTION__);             

        $Response = [];
        $Repo   =   $this->_em->getRepository('NodesFakerBundle:FakeObject');        
        
        //====================================================================//
        // Prepare List Filters List
        $Search     =   array(
            "node"      => $this->fake,
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
        $Fields =   $this->fake->getObjectFields($this->type);

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
            "total"   => $Repo->getTypeCount($this->fake,$this->type, $filter), 
            "current" => count($Data)
            );
        
        //====================================================================//
        // Return result
        return $Response;
    }
    
    /**
    *   @abstract     Return requested Customer Data
    *   @param        array   $id               Customers Id.  
    *   @param        array   $list             List of requested fields    
    */
    public function Get($id=NULL,$list=0)
    {
        global $kernel;
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__,__FUNCTION__);  
        
        //====================================================================//
        // Format List
        if (is_a($list, "ArrayObject")) {
            $list = $list->getArrayCopy();
        }
        
        //====================================================================//
        // Load Object
        $FakeObject = $this->fake->getObject($this->type, $id);
        if ( !$FakeObject ) {
            return False; 
        }
        
        //====================================================================//
        // Link to Fake Node Entity
        $Formater = $kernel->getContainer()
                ->get("OpenObject.Formater.Service");
        
        //====================================================================//
        // Load Requested Object Data
        $Out  =   $Formater->filterData($FakeObject->getData(), $list);
        
        //====================================================================//
        // Add Object Id to Data
        $Out["id"]    =   $id;
        return $Out; 
    }
        
    /**
    *   @abstract     Write or Create requested Customer Data
    *   @param        array   $id               Customers Id.  If NULL, Customer needs t be created.
    *   @param        array   $list             List of requested fields    
    *   @return       string  $id               Customers Id.  If NULL, Customer wasn't created.    
    */
    public function Set($id=NULL,$list=NULL)
    {
        global $kernel;        
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__,__FUNCTION__);
        
        //====================================================================//
        // Create Object if Needed
        if ( !$id ) {
            $FakeObject     =   $this->fake->createObject($this->type);
            if ($FakeObject) {
                $this->_em->persist($FakeObject);
                $this->_em->flush();
            }
            $id         =   $FakeObject->getIdentifier();
            $this->In   =   array();
        } else {
            $this->In   = $this->fake->getObjectData($this->type, $id);
        }
        
        //====================================================================//
        // Geneerate reduced Fields List
        $FieldList = $kernel->getContainer()
                ->get("OpenObject.Formater.Service")
                ->reduceFieldList($this->fake->getObjectFields($this->type));
        
        //====================================================================//
        // Run through all Received Data
        foreach ( $list as $FieldId => $FieldData) {
            
            //====================================================================//
            // Detect Simple Field Id
            if (in_array($FieldId, $FieldList)) {
                //====================================================================//
                // Update Requested Object Simple Data
                $this->In[$FieldId]    =    $FieldData;
                
                continue;
            }            

            //====================================================================//
            // Manage List Data
            //====================================================================//
            
            $this->SetList($FieldId, $FieldData, $FieldList);
            
        }
        
        //====================================================================//
        // Save Changes
        $this->fake->setObjectData($this->type, $id, $this->In);
        $this->_em->flush();        
        
        return $id;        
    }       

    /**
    *   @abstract   Delete requested Object
    *   @param      int         $id             Object Id.  If NULL, Object needs to be created.
    *   @return     int                         0 if KO, >0 if OK 
    */    
    public function Delete($id=NULL)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__,__FUNCTION__);  
        //====================================================================//
        // Load Object 
        $FakeObject   = $this->fake->getObject($this->type, $id);
        if ( !$FakeObject ) {
            return True;
        }
        //====================================================================//
        // Remove Object from Node
        $this->fake->removeObject($FakeObject);
        //====================================================================//
        // Delete Object
        $this->_em->remove($FakeObject);
        $this->_em->flush();
        
        return True;
    }       

    //====================================================================//
    // Class Tooling Functions
    //====================================================================//

    /**
     *      @abstract   Return name of this Object Class
     */
    public function getName()
    {
        return "Fake " . ucfirst($this->type);
    }

    public function SetList($ListName, $ListData, $FieldList)
    {
        //====================================================================//
        // Check List Data is Array
        if ( !is_array($ListData) && !is_a($ListData, "ArrayObject")){
            return;
        }        
        
        //====================================================================//
        // Create List Array If Needed
        if (!array_key_exists($ListName,$this->In)) {
            $this->In[$ListName] = array();
        }
            
        $Index = 0;
        //====================================================================//
        // Import List Items
        foreach ($ListData as $ItemData) {
            
            //====================================================================//
            // Create Line Array If Needed
            if (!array_key_exists($Index,$this->In[$ListName])) {
                $this->In[$ListName][$Index] = array();
            }    
            
            //====================================================================//
            // Import Items Field Data
            foreach ($ItemData as $FieldId => $FieldData) {

                //====================================================================//
                // Verify Field Id is Set for This Object
                if ( !in_array($FieldId . LISTSPLIT . $ListName, $FieldList)) {
                    continue;
                }   
                
                //====================================================================//
                // Store Field Data in Array
                $this->In[$ListName][$Index][$FieldId] = $FieldData;                

            }
            
            $Index++;

        }
        
    }          
    
}



?>
