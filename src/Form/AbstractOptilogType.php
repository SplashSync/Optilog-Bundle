<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\Optilog\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Splash\Connectors\Optilog\Models\RestHelper as API;
use Burgov\Bundle\KeyValueFormBundle\Form\Type\KeyValueType;
use Splash\Connectors\Optilog\Models\CarrierCodes;

/**
 * Base Form Type for Optilog Connectors Servers
 */
abstract class AbstractOptilogType extends AbstractType
{
    /**
     * Add Ws Host Url Field to FormBuilder
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addWsHostField(FormBuilderInterface $builder, array $options)
    {
        $builder
            //==============================================================================
            // Optilog Api Host Url
            ->add('WsHost', ChoiceType::class, array(
                'label' => "var.apiurl.label",
                'help_block' => "var.apiurl.desc",
                'required' => true,
                'translation_domain' => "OptilogBundle",
                'choices' => API::ENDPOINTS,
            ))
        ;

        return $this;
    }
    
    /**
     * Add Api User Field to FormBuilder
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addApiUserField(FormBuilderInterface $builder, array $options)
    {
        $builder
            //==============================================================================
            // Optilog Api User For Authentification
            ->add('ApiUser', TextType::class, array(
                'label' => "var.apiuser.label",
                'help_block' => "var.apiuser.desc",
                'required' => true,
                'translation_domain' => "OptilogBundle",
            ))
        ;

        return $this;
    }
    
    /**
     * Add Api Password Field to FormBuilder
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addApiPwdField(FormBuilderInterface $builder, array $options)
    {
        $builder
            //==============================================================================
            // Optilog Api Password For Authentification
            ->add('ApiPwd', TextType::class, array(
                'label' => "var.apipwd.label",
                'help_block' => "var.apipwd.desc",
                'required' => true,
                'translation_domain' => "OptilogBundle",
            ))
        ;

        return $this;
    }
    
    
    /**
     * Add Api Key Field to FormBuilder
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addApiKeyField(FormBuilderInterface $builder, array $options)
    {
        $builder
            //==============================================================================
            // Optilog Api Key For Authentification
            ->add('ApiKey', TextType::class, array(
                'label' => "var.apikey.label",
                'help_block' => "var.apikey.desc",
                'required' => true,
                'translation_domain' => "OptilogBundle",
            ))
        ;

        return $this;
    }
    
    
    /**
     * Add User Carriers Names Field to FormBuilder
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addCarriersListField(FormBuilderInterface $builder, array $options)
    {
        $builder
            //==============================================================================
            // Optilog Carriers Names => Codes For Authentification
            ->add('Carriers', KeyValueType::class, array(
                'label' => "var.carriers.label",
                'help_block' => "var.carriers.desc",
                'required' => false,
                'key_type' => TextType::class,
                'key_options' => array(
                    'label' => "Nom du Transporteur"
                ),
                'value_type' => ChoiceType::class,
                'value_options' => array(
                    'label' => "Code Optilog",
                    'choices' => array_flip(CarrierCodes::CODES)
                ),
                'translation_domain' => "OptilogBundle",
            ))
        ;

        return $this;
    }    
}
