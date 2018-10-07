<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\ExplorerBundle\Model;

use Splash\Bundle\Models\ConnectorInterface;

/**
 * Description of ConnectorAwareTrait
 *
 * @author nanard33
 */
trait ConnectorAwareTrait {
    
    /**
     * @var ConnectorInterface
     */
    private $connector;
    
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
            
    
}
