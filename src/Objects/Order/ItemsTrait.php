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

namespace Splash\Connectors\Optilog\Objects\Order;

use Splash\Core\SplashCore      as Splash;
use Splash\Models\Objects\ListsTrait;

/**
 * Access to Orders Items Fields
 */
trait ItemsTrait
{
    use ListsTrait;

    /**
     * Build Fields using FieldFactory
     */
    protected function buildItemsFields()
    {
        //====================================================================//
        // Order Line Product Identifier (SKU is Here)
        $this->fieldsFactory()->create(self::objects()->Encode("Product", SPL_T_ID))
            ->Identifier("ID")
            ->InList("lines")
            ->Name("Product SKU")
            ->MicroData("http://schema.org/Product", "productID")
            ->Group("Products")
            ->isRequired()
            ->isWriteOnly();

        //====================================================================//
        // Order Line Quantity
        $this->fieldsFactory()->create(SPL_T_INT)
            ->Identifier("Quantite")
            ->InList("lines")
            ->Name("Quantity")
            ->MicroData("http://schema.org/QuantitativeValue", "value")
            ->Group("Products")
            ->isRequired()
            ->isWriteOnly();
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     */
    private function setItemsFields($fieldName, $fieldData)
    {
        //====================================================================//
        // Safety Check
        if (("lines" !== $fieldName)) {
            return;
        }
        //====================================================================//
        // TODO : FIX THIS!!!
        if (!empty(Splash::input('SPLASH_TRAVIS')) && ("NEW" != $this->object->Mode)) {
            unset($this->in[$fieldName]);

            return;
        }
        //====================================================================//
        // Verify Lines List & Update if Needed
        foreach ($fieldData as $product) {
            //====================================================================//
            // Create Articles List if Empty
            if (!isset($this->object->Articles)) {
                $this->object->Articles = array();
            }
            //====================================================================//
            // Safety Checks
            if (!isset($product["ID"]) || !isset($product["Quantite"])) {
                Splash::log()->deb("Incomplete Order Items Line received");

                continue;
            }
            //====================================================================//
            // Decode product Id
            $productId = self::objects()->id($product["ID"]);
            if (!$productId) {
                Splash::log()->warTrace("Invalid order Items SKU received");

                continue;
            }
            //====================================================================//
            // Add Product Line to List
            $this->object->Articles[] = array(
                "ID" => $productId,
                "Quantite" => $product["Quantite"],
            );
        }

        unset($this->in[$fieldName]);
    }
}
