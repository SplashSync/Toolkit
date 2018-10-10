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

namespace App\ExplorerBundle\Fields;

use ArrayObject;

use Splash\Models\Fields\FieldsManagerTrait;

use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;


use App\ExplorerBundle\Form\MultilangType;

/**
 * Description of FieldsMapper
 *
 * @author nanard33
 */
class FormHelper {
    
    use FieldsManagerTrait;
    
    public static function formGroup(ArrayObject $Field)
    {
        if ( self::isListField($Field->id) ) {
            return   FieldsManager::isListField($Field->id)["listname"];
        } 
        if (!empty($Field->group) && is_scalar($Field->group)) {
            return   $Field->group;
        }
        return "default";
    }  
    
    public function formType(ArrayObject $Field)
    {
        switch($Field->type) {
            case SPL_T_BOOL:
                return CheckboxType::class;
                
            case SPL_T_INT:
                return IntegerType::class;
                
            case SPL_T_DOUBLE:
                return NumberType::class;
                
            case SPL_T_EMAIL:
                return EmailType::class;
                
            case SPL_T_CURRENCY:
                return CurrencyType::class;
                
            case SPL_T_LANG:
                return LanguageType::class;
                
            case SPL_T_URL:
                return UrlType::class;                
                
            case SPL_T_COUNTRY:
                return CountryType::class;    

            case SPL_T_DATE:
                return DateType::class;    
                
            case SPL_T_DATETIME:
                return DateTimeType::class;    

                
            case SPL_T_MVARCHAR:
            case SPL_T_MTEXT:
                return MultilangType::class;
                
        }
        return TextType::class;
    }      
    
    public function formOptions(ArrayObject $Field)
    {
        switch($Field->type) {
            case SPL_T_DATE:
            case SPL_T_DATETIME:
                $options = array(
                    'widget'                => 'single_text',
                    'input'                 => 'string',
                );
                break;
            default:
                $options = array();
                break;
        }
        
        return array_merge_recursive($options, array(
            //====================================================================//
            // Manage Required Tag      
            "required"                          =>   !empty($Field->required),
        ));
    }   

    public function showOptions($Field, $isList = false)
    {
        return array(
            //====================================================================//
            // Add List Flag     
            "splash_is_list"    =>   $isList,
            //====================================================================//
            // Add Fields Informations     
            "splash_field"      =>   $Field,
            //====================================================================//
            // Specify Sonata to Render Splash Specific Template     
            "template"          =>   '@AppExplorer/CRUD/base_show_field.html.twig'
        );
    }   
    
}
