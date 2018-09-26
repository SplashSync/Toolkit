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

use ArrayObject;

use Splash\Client\Splash;
use Nodes\FakerBundle\Entity\FakeNode;
use Nodes\FakerBundle\Services\FakerService;
use Doctrine\ORM\EntityManager;

/**
 * @abstract    Local Overiding Objects Manager for Splash Bundle
 * @author      B. Paquier <contact@splashsync.com>
 */   
class Local
{

//====================================================================//
// *******************************************************************//
//  MANDATORY CORE MODULE LOCAL FUNCTIONS
// *******************************************************************//
//====================================================================//
    
    /**
     *      @abstract       Return Local Server Parameters as Aarray
     *                      
     *      THIS FUNCTION IS MANDATORY 
     * 
     *      This function called on each initialisation of the module
     * 
     *      Result must be an array including mandatory parameters as strings
     *         ["WsIdentifier"]         =>>  Name of Module Default Language
     *         ["WsEncryptionKey"]      =>>  Name of Module Default Language
     *         ["DefaultLanguage"]      =>>  Name of Module Default Language
     * 
     *      @return         array       $parameters
     */
    public function Parameters()
    {
        return $this->getFakeNode()->getWsParameters();
    }    
    
    /**
     *      @abstract       Include Local Includes Files
     * 
     *      Include here any local files required by local functions. 
     *      This Function is called each time the module is loaded 
     * 
     *      There may be differents scenarios depending if module is 
     *      loaded as a library or as a NuSOAP Server. 
     * 
     *      This is triggered by global constant SPLASH_SERVER_MODE.
     * 
     *      @return         bool                     
     */
    public function Includes()
    {
        //====================================================================//
        // NOTHING TO DO         
        return True;
    }      

    /**
     *      @abstract       Return Local Server Self Test Result
     *                      
     *      THIS FUNCTION IS MANDATORY 
     * 
     *      This function called during Server Validation Process
     * 
     *      We recommand using this function to validate all functions or parameters
     *      that may be required by Objects, Widgets or any other modul specific action.
     * 
     *      Use Module Logging system & translation tools to retrun test results Logs
     * 
     *      @return         bool    global test result
     */
    public function SelfTest()
    {
        
        //====================================================================//
        //  Load Local Translation File
        Splash::translator()->load("main@local");          
        //====================================================================//
        //  Load - Server Parameters
        $Parameters = $this->getFakeNode()->getWsParameters();
        
        //====================================================================//
        //  Verify - Server Parameters Given
        if ( empty($Parameters) ) {
            return Splash::log()->err("ErrSelfTestNoParameters");
        }        
        
        //====================================================================//
        //  Verify - Server Identifier Given
        if ( !isset($Parameters["WsIdentifier"]) || empty($Parameters["WsIdentifier"]) ) {
            return Splash::log()->err("ErrSelfTestNoWsId");
        }        
                
        //====================================================================//
        //  Verify - Server Encrypt Key Given
        if ( !isset($Parameters["WsEncryptionKey"]) || empty($Parameters["WsEncryptionKey"]) ) {
            return Splash::log()->err("ErrSelfTestNoWsKey");
        }        

        Splash::log()->msg("MsgSelfTestOk");
        return True;
    }       
    
    /**
     * @abstract    Update Server Informations with local Data
     * 
     * @param   ArrayObject  $Informations   Informations Inputs
     * 
     * @return  ArrayObject
     */
    public function Informations($Informations)
    {
        //====================================================================//
        // Init Response Object
        $Response = $Informations;
        //====================================================================//
        // Load Server Informations
        $WsInformations =   $this->getFakeNode()->getWsInformations();
        //====================================================================//
        // Parse Server Informations
        foreach ($WsInformations as $key => $value) {
            $Response->$key = $value;
        }
        return $Response;
    }    
    
//====================================================================//
// *******************************************************************//
//  OVERRIDING CORE MODULE LOCAL FUNCTIONS
// *******************************************************************//
//====================================================================//    
    
    /**
     * @abstract    Build list of Available Objects
     * 
     * @return  array       $list           list array including all available Objects Type 
     */
    public function Objects()
    {
        //====================================================================//
        // Load Fake Node Objects List
        return    $this->getFakeNode()->getObjectsTypes();
    }   
    
    /**
     * @abstract    Get Specific Object Class
     *              This function is a router for all local object classes & functions
     * 
     * @params      string  $type       Specify Object Class Name
     * 
     * @return      LocalObject
     */
    public function Object($ObjectType)
    {    
        return new LocalObject($this->getFakeNode(), $this->getEntityManager(), $ObjectType);
    }

//====================================================================//
// *******************************************************************//
//  OVERRIDING CORE MODULE LOCAL FUNCTIONS
// *******************************************************************//
//====================================================================//    

    /**
     * @abstract    Get Current ACtive FakeNode
     * @return FakeNode
     */
    public function getFakeNode() {        
        global $kernel;

        return  $kernel
                ->getContainer()
                ->get("splash.nodes.faker.test.service")
                ->getByIdentifier();
    }
    
    /**
     * @abstract    Get Entity Manager
     * @return EntityManager
     */
    public function getEntityManager() {        
        global $kernel;

        return  $kernel->getContainer()
                ->get('doctrine')
                ->getManager();
    }    
}

?>
