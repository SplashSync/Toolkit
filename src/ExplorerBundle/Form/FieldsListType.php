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

namespace App\ExplorerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\ExplorerBundle\Fields\FormHelper;

/**
 * @abstract    Splash Objects Fields List Form Type
 */
class FieldsListType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {         
        
        //==============================================================================
        // Generate Forms Data for Each Field in Collection
        foreach ($options["fields"] as $Field) {
            //====================================================================//
            // Detect List Fields        
            $List   =   FormHelper::isListField($Field->type);
            //====================================================================//
            // Detect Edit or Show        
            if ($builder instanceof ShowMapper) {
                $options    =   FormHelper::showOptions($Field);
            } else {
                $options    =   FormHelper::formOptions($Field);
            }
            //====================================================================//
            // Generate Field Form Entry        
            $builder->add(
                    $List["fieldname"], 
                    FormHelper::formType($Field),
                    $options
                );            
        }
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        
        $resolver->setDefaults(array(
            'fields' => array(),
        ));
    }     
    
    /**
     * @return string
     */
    public function getName()
    {
        return 'Splash_Fields_List_Form';
    }
    
}