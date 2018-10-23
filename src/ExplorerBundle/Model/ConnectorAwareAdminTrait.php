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


/**
 * Description of ConnectorAwareTrait
 *
 * @author nanard33
 */
trait ConnectorAwareAdminTrait {
    
    /**
     * @abstract    Current Connector Type Name
     * @var string
     */
    private $connector;
    
    /**
     * @abstract    Current Server Id
     * @var string
     */
    private $serverId;
    
    /**
     * @abstract    Current Object Type
     * @var string
     */
    private $objectType;
    
    /**
     * @abstract    Objects Type
     * @var array
     */
    private $objectTypes;

    //====================================================================//
    // Admin Model Manager Managements
    //====================================================================//

    protected function configureModelManager()
    {
        //====================================================================//
        // Load Model Manager
        $ModelManager   =   $this->getConfigurationPool()->getContainer()->get("sonata.admin.manager.splash");
        //====================================================================//
        // Setup Model Manager     
        $ModelManager->setConnection($this->serverId);      
        //====================================================================//
        // Override Model Manager
        $this->setModelManager($ModelManager);
    }    
    
    //====================================================================//
    // Objects Managements
    //====================================================================//
    
    public function getObjectType()
    {
        //====================================================================//
        // Detect Object Type from Cache
        if (!empty($this->objectType)) {
            return $this->objectType;
        } 
        //====================================================================//
        // Detect Object Type from Request
        $this->objectType =   $this->getRequest()->getSession()->get("ObjectType");
        //====================================================================//
        // No Object Type? Take First Available from Connector
        if (empty($this->objectType)) {
            $this->objectTypes  =   $this->getModelManager()->getConnector()->objects();
            $this->objectType   =   array_shift($this->objectTypes);
        }
        return $this->objectType;
    }
    
    //====================================================================//
    // Basic Getters & Setters
    //====================================================================//
    
    /**
     * @abstract    Setup Connector Name 
     * @param   string  $ConnectorName
     * @return  $this
     */
    protected function setConnectorName(string $ConnectorName)
    {
        $this->connector    =   $ConnectorName;
        return $this;
    }
    
    public function getConnectorName()
    {
        return $this->connector;
    }
    
    /**
     * @abstract    Setup Splash Server Id
     * @param   string  $ConnexionName
     * @return  $this
     */
    protected function setServerId(string $ConnexionName)
    {
        $this->serverId    =   $ConnexionName;
        return $this;
    }
    
    public function getServerId()
    {
        return $this->serverId;
    }    
    

    
    
}
