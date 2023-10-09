<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) Splash Sync  <www.splashsync.com>
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
use Splash\Connectors\Optilog\Models\StatusHelper;
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
     *
     * @return $this
     */
    protected function addWsHostField(FormBuilderInterface $builder): self
    {
        $builder
            //==============================================================================
            // Optilog Api Host Url
            ->add('WsHost', ChoiceType::class, array(
                'label' => "var.apiurl.label",
                'help' => "var.apiurl.desc",
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
     *
     * @return $this
     */
    protected function addApiUserField(FormBuilderInterface $builder): self
    {
        $builder
            //==============================================================================
            // Optilog Api User For Authentification
            ->add('ApiUser', TextType::class, array(
                'label' => "var.apiuser.label",
                'help' => "var.apiuser.desc",
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
     *
     * @return $this
     */
    protected function addApiPwdField(FormBuilderInterface $builder): self
    {
        $builder
            //==============================================================================
            // Optilog Api Password For Authentification
            ->add('ApiPwd', TextType::class, array(
                'label' => "var.apipwd.label",
                'help' => "var.apipwd.desc",
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
     *
     * @return $this
     */
    protected function addApiKeyField(FormBuilderInterface $builder): self
    {
        $builder
            //==============================================================================
            // Optilog Api Key For Authentification
            ->add('ApiKey', TextType::class, array(
                'label' => "var.apikey.label",
                'help' => "var.apikey.desc",
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
     *
     * @return $this
     */
    protected function addApiOperationField(FormBuilderInterface $builder): self
    {
        $builder
            //==============================================================================
            // Optilog Api Key For Authentification
            ->add('ApiOp', TextType::class, array(
                'label' => "var.apiop.label",
                'help' => "var.apiop.desc",
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
     *
     * @return $this
     */
    protected function addMinOrderCreateDateField(FormBuilderInterface $builder): self
    {
        $builder
            //==============================================================================
            // Optilog Default Stock Location
            ->add('minOrderDate', DateTimeType::class, array(
                'label' => "var.minOrderDate.label",
                'help' => "var.minOrderDate.desc",
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
     *
     * @return $this
     */
    protected function addLocationField(FormBuilderInterface $builder): self
    {
        $builder
            //==============================================================================
            // Optilog Default Stock Location
            ->add('dfStock', TextType::class, array(
                'label' => "var.dfStock.label",
                'help' => "var.dfStock.desc",
                'required' => false,
                'translation_domain' => "OptilogBundle",
            ))
        ;

        return $this;
    }

    /**
     * Add Product Sku Mode Selector
     *
     * @param FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addProductSkuField(FormBuilderInterface $builder): self
    {
        $builder
            ->add('useProductsRawSku', CheckboxType::class, array(
                'label' => "var.useProductsRawSku.label",
                'help' => "var.useProductsRawSku.desc",
                'required' => false,
                'translation_domain' => "OptilogBundle",
            ))
        ;

        return $this;
    }

    /**
     * Add Extended Order Status Mode Selector
     *
     * @param FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addExtendedStatusField(FormBuilderInterface $builder): self
    {
        $builder
            ->add('useExtendedStatus', CheckboxType::class, array(
                'label' => "var.useExtendedStatus.label",
                'help' => "var.useExtendedStatus.desc",
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
     *
     * @return $this
     */
    protected function addCarriersListField(FormBuilderInterface $builder): self
    {
        $builder
            //==============================================================================
            // Optilog Carriers Names => Codes For Authentification
            ->add('Carriers', KeyValueType::class, array(
                'label' => "var.carriers.label",
                'help' => "var.carriers.desc",
                'required' => false,
                'key_type' => TextType::class,
                'key_options' => array(
                    'label' => "Nom du Transporteur",
                ),
                'value_type' => ChoiceType::class,
                'value_options' => array(
                    'label' => "Code Optilog",
                    'choices' => CarrierCodes::getCarrierChoices(),
                ),
                'translation_domain' => "OptilogBundle",
            ))
        ;

        return $this;
    }

    /**
     * Add User Stocks Names Field to FormBuilder
     *
     * @param FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addStocksListField(FormBuilderInterface $builder): self
    {
        $builder
            //==============================================================================
            // Optilog Carriers Names => Codes For Authentification
            ->add('Stocks', KeyValueType::class, array(
                'label' => "var.stocks.label",
                'help' => "var.stocks.desc",
                'required' => false,
                'key_type' => TextType::class,
                'key_options' => array(
                    'label' => "var.stocks.name",
                ),
                'value_type' => TextType::class,
                'value_options' => array(
                    'label' => "var.stocks.code",
                ),
                'translation_domain' => "OptilogBundle",
            ))
        ;

        return $this;
    }

    /**
     * Add Origin Filters Field to FormBuilder
     *
     * @param FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addOriginFilterField(FormBuilderInterface $builder): self
    {
        $choices = array(
            "var.origin.default" => "pass",
            "var.origin.rejected" => "REJECTED",
        );

        $builder
            ->add('OrderOrigins', KeyValueType::class, array(
                'label' => "var.origin.label",
                'help' => "var.origin.desc",
                'required' => false,
                'key_type' => TextType::class,
                'key_options' => array(
                    'label' => "Origin",
                ),
                'value_type' => ChoiceType::class,
                'value_options' => array(
                    'label' => "Action",
                    'choices' => $choices,
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
     *
     * @return $this
     */
    protected function addRandomStocksField(FormBuilderInterface $builder): self
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
     *
     * @return $this
     */
    protected function addForcedOrderStatusField(FormBuilderInterface $builder): self
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
                    'choices' => array_flip(StatusHelper::getAllNames()),
                ),
                'translation_domain' => "OptilogBundle",
            ))
        ;

        return $this;
    }
}
