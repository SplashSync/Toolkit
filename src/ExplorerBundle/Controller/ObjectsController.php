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
class ObjectsController extends CRUDController {
    
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
        //====================================================================//
        // Render Connector Profile Page
        return $this->render("@AppExplorer/Objects/list.html.twig", array(
            'action'    => 'list',
            'admin'     =>  $this->admin,
            "ObjectType"=>  $ObjectType,
            "objects"   =>  $this->admin->getModelManager()->getObjectsDefinition(),
            "fields"    =>  $this->admin->getModelManager()->getObjectFields($ObjectType),
            "list"      =>  $List,
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
