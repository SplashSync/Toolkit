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

namespace App\ExplorerBundle\Controller;

use ArrayObject;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sonata\AdminBundle\Controller\CRUDController;

use Splash\Core\SplashCore as Splash;
use Splash\Bundle\Models\ConnectorInterface;

/**
 * Description of ObjectCRUDController
 *
 * @author nanard33
 */
class ObjectsCRUDController extends CRUDController {
    
    private $Objects    = array();
    
    private $ObjectType = null;

    /**
     * @abstract    Setup Model Manager
     *
     * @return ConnectorInterface
     * @throws Exception    If Connector Init Fails
     */
    private function setupModelManager()
    {
        $ModelManager   =   $this->container->get("sonata.admin.manager.splash");
        $ModelManager->setObjectType($this->ObjectType);
        $this->admin->setModelManager($ModelManager);
    }  

    /**
     * @abstract    Setup Connactor
     *
     * @return ConnectorInterface
     * @throws Exception    If Connector Init Fails
     */
    private function setupConnector(Request $request) : ConnectorInterface
    {
        
        $this->container->get("splash.connectors.manager");
        
        //====================================================================//
        // Connect to Connector
        if (!$this->container->has($this->admin->getConnectorName())) {
            throw new \Exception("Connector Service not Found : " . $this->admin->getClass());
        } 
        $Connector  =   $this->container->get($this->admin->getConnectorName());
        //====================================================================//
        // Setup Connector
        $Configuration  =   $this->container->getParameter("splash");
        $Connector->setConfiguration($Configuration["connections"][$this->admin->getConnectionName()]);
        Splash::setLocalClass($Connector);
        
        //====================================================================//
        // Load Objects Informations
        foreach ($Connector->objects() as $ObjectType) {
            $this->Objects[$ObjectType]    =   $Connector->object($ObjectType);            
        }        
        //====================================================================//
        // Detect Object Type        
        $this->ObjectType =   $request->get("ObjectType");
        if (empty($this->ObjectType)) {
            $ObjectTypes        =   array_keys($this->Objects);
            $this->ObjectType   =   array_shift($ObjectTypes);
        }
        return $Connector;
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
     * Switch Between Object Types
     *
     * @return Response
     */
    public function switchAction(Request $request = null)
    {
        $ObjectType     =   $request->get("ObjectType");
        $ObjectTypes    =   $this->admin->getModelManager()->getObjects();
        
        if ($ObjectType && in_array($ObjectType, $ObjectTypes))  {
            $request->getSession()->set("ObjectType" , $ObjectType);
        }

        return $this->redirectToList();
                
    }
        
    /**
     * List action.
     *
     * @throws AccessDeniedException If access is not granted
     *
     * @return Response
     */
    public function listAction(Request $request = null)
    {
        //====================================================================//
        // Detect Current Object Type
        $ObjectType  =  $this->admin->getObjectType();
        $this->admin->getModelManager()->setObjectType($ObjectType);   
        //====================================================================//
        // Read Object List        
        $List = $this->admin->getModelManager()->findBy($ObjectType);
        $Meta   =   isset($List["meta"]) ? $List["meta"] : array();
        unset($List["meta"]);
//Splash::log()->www("ObjectList", $List); 
     
        //====================================================================//
        // Render Connector Profile Page
        return $this->render("@AppExplorer/Objects/list.html.twig", array(
            'action'    => 'list',
            'admin'     =>  $this->admin,
            "ObjectType"=>  $ObjectType,
            "objects"   =>  $this->admin->getModelManager()->getObjectsDefinition(),
            "fields"    =>  $this->admin->getModelManager()->getObjectFields($ObjectType),
            "list"      =>  $List,
//            "infos"     =>  $Informations,
//            "config"    =>  Splash::configuration(),
//            "results"   =>  $Results,
//            "selftest"  =>  $SelfTest_Log,
//            "ping"      =>  $PingTest_Log,
//            "connect"   =>  $ConnectTest_Log,
//            "widgets"   =>  Splash::Widgets(),
            "log"       =>  Splash::log()->GetHtmlLog(true),
        ));
    }
    
    /**
     * Show action.
     *
     * @throws AccessDeniedException If access is not granted
     *
     * @return Response
     */
    public function showAction($id = null, Request $request = null)
    {
        //====================================================================//
        // Detect Current Object Type
        $this->admin->getModelManager()->setObjectType($this->admin->getObjectType());           
        //====================================================================//
        // Base Admin Action
        return parent::showAction($id);        
        
        //====================================================================//
        // Detect Current Object Type
        $ObjectType  =  $this->admin->getObjectType();           
        
        //====================================================================//
        // Setup Connector
        $Connector  =   $this->setupConnector($request);

        //====================================================================//
        // Prepare Readable Fields List
        $Fields = $this->reduceFieldList(
                Splash::object($this->ObjectType)->fields(), 
                true, 
                false
            );

        
        //====================================================================//
        // Read Object Data      
        $Data   =   Splash::object($this->ObjectType)->get($id, $Fields);

//Splash::log()->www("Object Data", $Data); 

        //====================================================================//
        // Render Connector Profile Page
        return $this->render("@AppExplorer/Objects/show.html.twig", array(
            'action'    => 'list',
            "profile"   =>  $Connector->getProfile(),
            "fields"    =>  $this->Objects[$this->ObjectType]->fields(),            
            "object"    =>  $this->Objects[$this->ObjectType],
            "ObjectType"=>  $this->ObjectType,
            "objects"   =>  $this->Objects,
            "data"      =>  $Data,
            "log"       =>  Splash::log()->GetHtmlLog(true),
        ));
    }    
    
    /**
     * Edit action.
     *
     * @throws AccessDeniedException If access is not granted
     *
     * @return Response
     */
    public function editAction($id = null)
    {
        //====================================================================//
        // Detect Current Object Type
        $this->admin->getModelManager()->setObjectType($this->admin->getObjectType());           
        //====================================================================//
        // Base Admin Action
        return parent::editAction($id);
    }        
    
    /**
     * Create action.
     *
     * @throws AccessDeniedException If access is not granted
     *
     * @return Response
     */
    public function createAction()
    {
        //====================================================================//
        // Detect Current Object Type
        $this->admin->getModelManager()->setObjectType($this->admin->getObjectType());           
        //====================================================================//
        // Base Admin Action
        return parent::createAction();
    }
    
}
