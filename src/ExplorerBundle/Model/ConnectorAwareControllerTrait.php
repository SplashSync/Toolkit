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

namespace App\ExplorerBundle\Model;

use Splash\Bundle\Models\ConnectorInterface;

use App\ExplorerBundle\Entity\SplashServer;

/**
 * Description of ConnectorAwareControllerTrait
 *
 * @author nanard33
 */
trait ConnectorAwareControllerTrait {

   /**
     * @abstract    Current Connector Service
     * @var         ConnectorInterface
     */
    private $connector;
    
    /**
     * @abstract    Setup Connactor
     *
     * @return ConnectorInterface
     * @throws Exception    If Connector Init Fails
     */
    protected function setupConnector() : ConnectorInterface
    {
        //====================================================================//
        // Connect to Connector
        // 
        //====================================================================//
        // Connect to Connector
        if (!$this->container->has($this->admin->getConnectorName())) {
            throw new Exception("Connector Service not Found : " . $this->admin->getClass());
        } 
        $this->connector  =   $this->container->get($this->admin->getConnectorName());
        //====================================================================//
        // Setup Connector
        $Configuration  =   $this->container->getParameter("splash");
        $this->connector->setConfiguration($Configuration["connections"][$this->admin->getServerId()]);
//        Splash::setLocalClass($this->connector);
        
        return $this->connector;
    }

    /**
     * @abstract    Update Server Configuration
     * @param array $Configuration 
     * @return bool
     */
    protected function updateServerConfiguration(array $Configuration) : bool
    {
        //==============================================================================
        // Load DataBase configuration if Already Exists
        $DbConfiguration    =   $this->getDoctrine()
                ->getRepository("AppExplorerBundle:SplashServer")
                ->findOneByIdentifier($this->admin->getServerId());            
        
        //==============================================================================
        // Create DataBase Configuration
        if (empty($DbConfiguration)){
            $DbConfiguration    =   new SplashServer();
            $DbConfiguration->setIdentifier($this->admin->getServerId());
            $this->getDoctrine()->getManager()->persist($DbConfiguration);
        }
        //==============================================================================
        // Update
        $DbConfiguration->setSettings($Configuration);
        $this->getDoctrine()->getManager()->flush();
        
        return true;
    }
    
}
