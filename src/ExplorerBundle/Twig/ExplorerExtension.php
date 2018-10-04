<?php

namespace App\ExplorerBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ExplorerExtension extends AbstractExtension
{
    
    public function getFilters()
    {
        return array(
            new TwigFilter('isIdField',         array($this, 'isIdField')),
            new TwigFilter('getIdFieldType',    array($this, 'getIdFieldType')),
            new TwigFilter('getIdFieldData',    array($this, 'getIdFieldData')),
            new TwigFilter('isListField',       array($this, 'isListField')),
            new TwigFilter('getListFieldName',  array($this, 'getListFieldName')),
            new TwigFilter('getListFieldData',  array($this, 'getListFieldData')),
            new TwigFilter('filetype',          array($this, "filetypeFilter")),            
        );
    }

    /**
     *      @abstract   Identify if a string is an Object Identifier Data
     *      @param      string      $In             Id Field String
     *      @return     boolean     $result         
     */
    public function isIdField($In)
    {
        //====================================================================//
        // Safety Check 
        if (empty($In)) {
            return False;
        }        
        //====================================================================//
        // Detects Lists
        $list = explode ( IDSPLIT , $In );
        if (is_array($list) && (count($list)==2) ) {
            return True;
        }
        return False;
    }

    /**
     *      @abstract   Decode a string to extract Object Identifier Data Type
     *      @param      string      $In             Id Field String
     *      @return     boolean     $result         
     */
    public function getIdFieldType($In)
    {
        //====================================================================//
        // Safety Check 
        if (empty($In)) {
            return False;
        }        
        //====================================================================//
        // Detects Lists
        $list = explode ( IDSPLIT , $In );
        if (is_array($list) && (count($list)==2) ) {
            //====================================================================//
            // If List Detected, Return Field Object Type
            return $list[0];
        }
        return False;
    }

    /**
     *      @abstract   Decode a string to extract Object Identifier Data Type
     *      @param      string      $In             Id Field String
     *      @return     boolean     $result         
     */
    public function getIdFieldData($In)
    {
        //====================================================================//
        // Safety Check 
        if (empty($In)) {
            return False;
        }        
        //====================================================================//
        // Detects Lists
        $list = explode ( IDSPLIT , $In );
        if (is_array($list) && (count($list)==2) ) {
            //====================================================================//
            // If List Detected, Return Field Object Data
            return $list[1];
        }
        return False;
    }
    
    /**
     *      @abstract   Identify if a string is a List Field String
     *      @param      string      $In             List Field String
     *      @return     boolean     $result         
     */
    public function isListField($In)
    {
        //====================================================================//
        // Safety Check 
        if (empty($In)) {
            return False;
        }        
        //====================================================================//
        // Detects Lists
        $list = explode ( LISTSPLIT , $In );
        if (is_array($list) && (count($list)==2) ) {
            return True;
        }
        return False;
    }

    /**
     *      @abstract   Decode a list string to extract Field Identifier
     *      @param      string      $In             List Field String
     *      @return     boolean     $result         
     */
    public function getListFieldData($In)
    {
        //====================================================================//
        // Safety Check 
        if (empty($In)) {
            return False;
        }        
        //====================================================================//
        // Detects Lists
        $list = explode ( LISTSPLIT , $In );
        if (is_array($list) && (count($list)==2) ) {
            //====================================================================//
            // If List Detected, Return Field Identifier
            return $list[0];
        }
        return False;
    }

    /**
     *      @abstract   Decode a string to extract List Name String
     *      @param      string      $In             List Field String
     *      @return     boolean     $result         
     */
    public function getListFieldName($In)
    {
        //====================================================================//
        // Safety Check 
        if (empty($In)) {
            return False;
        }        
        //====================================================================//
        // Detects Lists
        $list = explode ( LISTSPLIT , $In );
        if (is_array($list) && (count($list)==2) ) {
            //====================================================================//
            // If List Detected, Return List Name
            return $list[1];
        }
        return False;
    }
    
    public function filetypeFilter($value)
    {
        return pathinfo($value, PATHINFO_EXTENSION);
    }     
    
    public function getName()
    {
        return 'App_Explorer_Twig_Extension';
    }
}