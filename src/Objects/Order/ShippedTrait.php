<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2020 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\Optilog\Objects\Order;

use Splash\Connectors\Optilog\Models\RestHelper as API;

/**
 * Access to Order Shipped Fields
 */
trait ShippedTrait
{
    /**
     * Build Fields using FieldFactory
     */
    protected function buildShippedFields(): void
    {
        //====================================================================//
        // Check if we are on API V2
        if (!API::isApiV2Mode()) {
            return;
        }

        //====================================================================//
        // Order Line Product Identifier (SKU is Here)
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("ID")
            ->InList("shipped")
            ->Name("Product SKU")
            ->MicroData("http://schema.org/OrderItem", "orderItemNumber")
            ->Group("Expéditions")
            ->isReadOnly()
        ;

        //====================================================================//
        // Order Line Quantity
        $this->fieldsFactory()->create(SPL_T_INT)
            ->Identifier("Quantite")
            ->InList("shipped")
            ->Name("Ordered Qty")
            ->MicroData("http://schema.org/OrderItem", "orderQuantity")
            ->Group("Expéditions")
            ->isReadOnly()
        ;

        //====================================================================//
        // Order Line Quantity
        $this->fieldsFactory()->create(SPL_T_INT)
            ->Identifier("Servie")
            ->InList("shipped")
            ->Name("Shipped Qty")
            ->MicroData("http://schema.org/OrderItem", "orderItemStatus")
            ->Group("Expéditions")
            ->isReadOnly()
        ;
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    protected function getShippedFields($key, $fieldName): void
    {
        //====================================================================//
        // Check if List field & Init List Array
        $fieldId = self::lists()->InitOutput($this->out, "shipped", $fieldName);
        if (!$fieldId) {
            return;
        }
        //====================================================================//
        // Verify List is Not Empty
        if (!isset($this->object->Articles) || !is_array($this->object->Articles)) {
            return;
        }
        //====================================================================//
        // Fill List with Data
        foreach ($this->object->Articles as $index => $product) {
            //====================================================================//
            // READ Fields
            switch ($fieldId) {
                //====================================================================//
                // Order Line Direct Reading Data
                case 'ID':
                    $value = $product->{$fieldId};

                    break;
                case 'Quantite':
                case 'Servie':
                    $value = isset($product->{$fieldId}) ? (int) $product->{$fieldId} : 0;

                    break;
                default:
                    return;
            }
            //====================================================================//
            // Insert Data in List
            self::lists()->Insert($this->out, "shipped", $fieldName, $index, $value);
        }

        unset($this->in[$key]);
    }
}
