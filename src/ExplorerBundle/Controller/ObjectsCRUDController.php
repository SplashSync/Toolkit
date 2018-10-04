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
     * List action.
     *
     * @throws AccessDeniedException If access is not granted
     *
     * @return Response
     */
    public function listAction(Request $request = null)
    {
        //====================================================================//
        // Setup Connector
        $Connector  =   $this->setupConnector($request);


        //====================================================================//
        // Read Object List        
        $List   =   Splash::object($this->ObjectType)->objectsList();
        $Meta   =   isset($List["meta"]) ? $List["meta"] : array();
        unset($List["meta"]);
Splash::log()->www("ObjectList", $List); 
     
        //====================================================================//
        // Render Connector Profile Page
        return $this->render("@AppExplorer/Objects/list.html.twig", array(
            'action'    => 'list',
            'admin'     =>  $this->admin,
            "ObjectType"=>  $this->ObjectType,
            "objects"   =>  $this->Objects,
            "fields"    =>  $this->Objects[$this->ObjectType]->fields(),
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

Splash::log()->www("Object Data", $Data); 

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
     * Show action.
     *
     * @throws AccessDeniedException If access is not granted
     *
     * @return Response
     */
    public function editAction($id = null)
    {
        //====================================================================//
        // Setup Connector
        $Connector  =   $this->setupConnector($this->getRequest());
        //====================================================================//
        // Setup Model Manager
        $this->setupModelManager();
        
        return parent::editAction($id);
        
        //====================================================================//
        // the key used to lookup the template
        $templateKey = 'edit';
        //====================================================================//
        // Prepare Writable Fields List
        $Fields = $this->reduceFieldList(
                Splash::object($this->ObjectType)->fields(), 
                true, 
                true
            );
        //====================================================================//
        // Read Object Data      
        $existingObject   =   Splash::object($this->ObjectType)->get($id, $Fields);
        if (!$existingObject) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id: %s', $id));
        }
        if (is_array($existingObject)) {
            $existingObject = new ArrayObject($existingObject, ArrayObject::ARRAY_AS_PROPS);
        }
        
        //====================================================================//
        // Pre Edit Event      
        $preResponse = $this->preEdit($request, $existingObject);
        if (null !== $preResponse) {
            return $preResponse;
        }

        $this->admin->setSubject($existingObject);
        
        $form = $this->admin->getForm();
        

        if (!\is_array($fields = $this->admin->getForm()->all()) || 0 === \count($fields)) {
            throw new \RuntimeException(
                'No editable field defined. Did you forget to implement the "configureFormFields" method?'
            );
        }

        $form = $this->admin->getForm();
        $form->setData( $existingObject);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {
                $submittedObject = $form->getData();
                $this->admin->setSubject($submittedObject);

                try {
                    $existingObject = $this->admin->update($submittedObject);

                    if ($this->isXmlHttpRequest()) {
                        return $this->renderJson([
                            'result' => 'ok',
                            'objectId' => $id,
                            'objectName' => $this->escapeHtml($this->admin->toString($existingObject)),
                        ], 200, []);
                    }

                    $this->addFlash(
                        'sonata_flash_success',
                        $this->trans(
                            'flash_edit_success',
                            ['%name%' => $this->escapeHtml($this->admin->toString($existingObject))],
                            'SonataAdminBundle'
                        )
                    );

                    // redirect to edit mode
                    return $this->redirectTo($existingObject);
                } catch (ModelManagerException $e) {
                    $this->handleModelManagerException($e);

                    $isFormValid = false;
                } catch (LockException $e) {
                    $this->addFlash('sonata_flash_error', $this->trans('flash_lock_error', [
                        '%name%' => $this->escapeHtml($this->admin->toString($existingObject)),
                        '%link_start%' => '<a href="'.$this->admin->generateObjectUrl('edit', $existingObject).'">',
                        '%link_end%' => '</a>',
                    ], 'SonataAdminBundle'));
                }
            }

            // show an error message if the form failed validation
            if (!$isFormValid) {
                if (!$this->isXmlHttpRequest()) {
                    $this->addFlash(
                        'sonata_flash_error',
                        $this->trans(
                            'flash_edit_error',
                            ['%name%' => $this->escapeHtml($this->admin->toString($existingObject))],
                            'SonataAdminBundle'
                        )
                    );
                }
            } elseif ($this->isPreviewRequested()) {
                // enable the preview template if the form was valid and preview was requested
                $templateKey = 'preview';
                $this->admin->getShow();
            }
        }

        $formView = $form->createView();
        // set the theme for the current Admin Form
//        $this->setFormTheme($formView, $this->admin->getFormTheme());

        // NEXT_MAJOR: Remove this line and use commented line below it instead
        $template = $this->admin->getTemplate($templateKey);
        // $template = $this->templateRegistry->getTemplate($templateKey);

        return $this->renderWithExtraParams($template, [
            'action' => 'edit',
            'form' => $formView,
            'object' => $existingObject,
            'objectId' => $id,
        ], null);
    }        
    

    
}
