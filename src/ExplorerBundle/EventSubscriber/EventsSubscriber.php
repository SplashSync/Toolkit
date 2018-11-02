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

namespace App\ExplorerBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Splash\Bundle\Events\ObjectsCommitEvent;
use Splash\Bundle\Services\ConnectorsManager;

/**
 * Symfony Events Subscriber
 */
class EventsSubscriber implements EventSubscriberInterface
{
    
    /**
     * @abstract    Splash Connectors Manager
     * @var ConnectorsManager
     */
    private $Manager;
    
    //====================================================================//
    //  CONSTRUCTOR
    //====================================================================//
    
    /**
     * @abstract    Service Constructor
     */
    public function __construct(ConnectorsManager $Manager)
    {
        //====================================================================//
        // Store Splash Connectors Manager
        $this->Manager  =   $Manager;
    }
    
    //====================================================================//
    //  SUBSCRIBER
    //====================================================================//
    
    /**
     * @abstract    Configure Event Subscriber
     * @return  void
     */
    public static function getSubscribedEvents()
    {
        return array(
            // Standalone Events
            ObjectsCommitEvent::NAME   => array(
               array('onObjectCommit', 100)
            ),
        );
    }

    //====================================================================//
    //  EVENTS ACTIONS
    //====================================================================//

    /**
     * @abstract    On Standalone Object Commit Event
     * @param   ObjectsCommitEvent $event
     * @return  void
     */
    public function onObjectCommit(ObjectsCommitEvent $event)
    {
        //====================================================================//
        // Detect Pointed Server Host
        $ServerId   = $this->Manager->hasWebserviceConfiguration($event->getServerId());
        $Host       = $this->Manager->getWebserviceHost($ServerId);
        //====================================================================//
        // If Server Host is False or Empty 
        // => Stop Event Propagation to Avoid Tying to Commit
        if (!$Host) {
            $event->stopPropagation();
        }
    }
}
