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

use Burgov\Bundle\KeyValueFormBundle\Form\Type\KeyValueType;
use Splash\Connectors\Optilog\Models\CarrierCodes;
use Splash\Connectors\Optilog\Models\RestHelper as API;
use Splash\Connectors\Optilog\Models\StatusCodes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

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
     * Add Api Key Field to FormBuilder
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addApiOperationField(FormBuilderInterface $builder, array $options)
    {
        $builder
            //==============================================================================
            // Optilog Api Key For Authentification
            ->add('ApiOp', TextType::class, array(
                'label' => "var.apiop.label",
                'help_block' => "var.apiop.desc",
                'required' => true,
                'translation_domain' => "OptilogBundle",
            ))
        ;

        return $this;
    }

    /**
     * Add Order Min Created Date & Time Filter to FormBuilder
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addMinOrderCreateDateField(FormBuilderInterface $builder, array $options)
    {
        $builder
            //==============================================================================
            // Optilog Default Stock Location
            ->add('minOrderDate', DateTimeType::class, array(
                'label' => "var.minOrderDate.label",
                'help_block' => "var.minOrderDate.desc",
                'widget' => 'single_text',
                'required' => false,
                'translation_domain' => "OptilogBundle",
            ))
        ;

        return $this;
    }

    /**
     * Add Default Stock Location Field to FormBuilder
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addLocationField(FormBuilderInterface $builder, array $options)
    {
        $builder
            //==============================================================================
            // Optilog Default Stock Location
            ->add('dfStock', TextType::class, array(
                'label' => "var.dfStock.label",
                'help_block' => "var.dfStock.desc",
                'required' => false,
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
                    'label' => "Nom du Transporteur",
                ),
                'value_type' => ChoiceType::class,
                'value_options' => array(
                    'label' => "Code Optilog",
                    'choices' => array_flip(CarrierCodes::CODES),
                ),
                'translation_domain' => "OptilogBundle",
            ))
        ;

        return $this;
    }

    /**
     * Add List of SKUs for Random Stocks to FormBuilder
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addRandomStocksField(FormBuilderInterface $builder, array $options)
    {
        $builder
            //==============================================================================
            // Optilog Carriers Names => Codes For Authentification
            ->add('RandomStocks', KeyValueType::class, array(
                'label' => "[DEBUG] Random Products Stocks",
                'required' => false,
                'key_type' => TextType::class,
                'key_options' => array(
                    'label' => "SKU du Produit",
                ),
                'value_type' => CheckboxType::class,
                'value_options' => array(
                    'label' => "Random Stock",
                ),
                'translation_domain' => "OptilogBundle",
            ))
        ;

        return $this;
    }

    /**
     * Add List of Orders ID where Status is Forced to FormBuilder
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addForcedOrderStatusField(FormBuilderInterface $builder, array $options)
    {
        $builder
            //==============================================================================
            // Optilog Carriers Names => Codes For Authentification
            ->add('ForcedStatus', KeyValueType::class, array(
                'label' => "[DEBUG] Force Orders Status",
                'required' => false,
                'key_type' => TextType::class,
                'key_options' => array(
                    'label' => "Order Number",
                ),
                'value_type' => ChoiceType::class,
                'value_options' => array(
                    'label' => "Forced Status",
                    'choices' => array_flip(StatusCodes::SPLASH),
                ),
                'translation_domain' => "OptilogBundle",
            ))
        ;

        return $this;
    }
}
