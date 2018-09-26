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

namespace Splash\Connectors\FakerBundle\Objects;

/**
 * Description of Generic
 *
 * @author nanard33
 */
class Generic {
    
    /**
     * @var string
     */
    private $name;
         
    /**
     * @var string
     */
    private $format;
    
    /**
     * @param string $name
     * @param string $format
     */
    public function setConfiguration(string $name, string $format)
    {
        $this->name     =   $name;
        $this->format   =   $format;
    }    
  
    
//    public function __construct(string $name, string $format)
//    {
//        
//    }    
    //put your code here
}
