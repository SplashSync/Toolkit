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

namespace Splash\Connectors\FakerBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

//use Splash\Bundle\Connectors\Standalone;
use Splash\Bundle\Events\ObjectsListingEvent;

//use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Description of FakerEventsSuscriber
 *
 * @author nanard33
 */
class FakerEventsSuscriber implements EventSubscriberInterface {
    
    /**
     * @abstract    Faker Bundle Configuration
     * @var array
     */
    private $config;    
    
//====================================================================//
//  CONSTRUCTOR
//====================================================================//
    
    /**
     * @abstract    Service Constructor
     */    
    public function __construct(array $Configuration) { 
        //====================================================================//
        // Store Faker Service Configuration
        $this->config       =   $Configuration;        
    }     
    
    
    /**
     * @abstract    Configure Event Subscriber
     * @return  void
     */
    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return array(
            ObjectsListingEvent::NAME   => array(
               array('onObjectListing', 0)
            ),
        );
    }

    /**
     * @abstract    On Standalone Object Listing Event
     * @param   ObjectsListingEvent $event
     * @return  void
     */
    public function onObjectListing(ObjectsListingEvent $event)
    {
        //====================================================================//
        // Walk on Configuration to Add Objects        
        foreach ($this->config["objects"] as $Object) {
            $event->addObjectType($Object["id"], "splash.connector.faker.object." . $Object["id"] );
        }
    }
}
