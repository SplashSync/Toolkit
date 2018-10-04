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

use Symfony\Component\HttpFoundation\Response;

use Sonata\AdminBundle\Controller\CRUDController;

use Splash\Core\SplashCore as Splash;
use Splash\Bundle\Models\ConnectorInterface;

/**
 * Description of ObjectCRUDController
 *
 * @author nanard33
 */
class ProfileCRUDController extends CRUDController {
    
    /**
     * @abstract    Setup Connactor
     *
     * @return ConnectorInterface
     * @throws Exception    If Connector Init Fails
     */
    private function setupConnector() : ConnectorInterface
    {
        //====================================================================//
        // Connect to Connector
        if (!$this->container->has($this->admin->getConnectorName())) {
            throw new Exception("Connector Service not Found : " . $this->admin->getClass());
        } 
        $Connector  =   $this->container->get($this->admin->getConnectorName());
        //====================================================================//
        // Setup Connector
        $Configuration  =   $this->container->getParameter("splash");
        $Connector->setConfiguration($Configuration["connections"][$this->admin->getConnectionName()]);
        Splash::setLocalClass($Connector);
        
        return $Connector;
    }    
    
    /**
     * List action.
     *
     * @throws AccessDeniedException If access is not granted
     *
     * @return Response
     */
    public function listAction()
    {
        //====================================================================//
        // Setup Connector
        $Connector  =   $this->setupConnector();

        //====================================================================//
        // Load Connector Profile
        $Profile    = $Connector->getProfile();
        
        $Results = array();
        
        //====================================================================//
        // Execute Splash Self-Test
        $Results['selftest'] = $Connector->selfTest();
        if ($Results['selftest']) {
            Splash::log()->msg("Self-Test Passed");
        }
        $SelfTest_Log = Splash::log()->GetHtmlLog(true);

        //====================================================================//
        // Execute Splash Ping Test
        $Results['ping']    = $Connector->ping();
        $PingTest_Log       = Splash::log()->GetHtmlLog(true);
        
        //====================================================================//
        // Execute Splash Connect Test
        $Results['connect'] = $Connector->connect();
        $ConnectTest_Log    = Splash::log()->GetHtmlLog(true);

        //====================================================================//
        // Load Connector Informations
        $Informations    = array();
        if ($Results['ping'] && $Results['connect']) {
            $Informations    = Splash::informations();
        }
        
        //====================================================================//
        // Load Objects Informations
        $Objects   =   array();
        foreach ($Connector->objects() as $ObjectType) {
            $Objects[$ObjectType]    =   $Connector->object($ObjectType);            
        }
     
        //====================================================================//
        // Render Connector Profile Page
        return $this->render("@AppExplorer/Profile/list.html.twig", array(
            'action'    => 'list',
            'admin'     =>  $this->admin,
            "profile"   =>  $Connector->getProfile(),
            "infos"     =>  $Informations,
            "config"    =>  Splash::configuration(),
            "results"   =>  $Results,
            "selftest"  =>  $SelfTest_Log,
            "ping"      =>  $PingTest_Log,
            "connect"   =>  $ConnectTest_Log,
            "objects"   =>  $Objects,
//            "widgets"   =>  Splash::Widgets(),
        ));
    }
    
    /**
     * Show action.
     *
     * @throws AccessDeniedException If access is not granted
     *
     * @return Response
     */
    public function showAction($id = null)
    {
        //====================================================================//
        // Setup Connector
        $Connector  =   $this->setupConnector();

        //====================================================================//
        // Render Connector Profile Page
        return $this->render("@AppExplorer/Profile/show.html.twig", array(
            'action'    => 'list',
            "profile"   =>  $Connector->getProfile(),
            "object"    =>  Splash::object($id),
            "log"       =>  Splash::log()->GetHtmlLog(true),
        ));
    }    
}
