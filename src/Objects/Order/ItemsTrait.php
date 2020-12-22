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
    protected function buildItemsFields(): void
    {
        //====================================================================//
        // Order Line Product Identifier (SKU is Here)
        $this->fieldsFactory()->create((string) self::objects()->encode("Product", SPL_T_ID))
            ->Identifier("ID")
            ->InList("lines")
            ->Name("Product SKU")
            ->MicroData("http://schema.org/Product", "productID")
            ->Group("Products")
            ->isRequired()
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());

        //====================================================================//
        // Order Line Quantity
        $this->fieldsFactory()->create(SPL_T_INT)
            ->Identifier("Quantite")
            ->InList("lines")
            ->Name("Quantity")
            ->MicroData("http://schema.org/QuantitativeValue", "value")
            ->Group("Products")
            ->isRequired()
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());

        //====================================================================//
        // Order Line Served Quantity
        $this->fieldsFactory()->create(SPL_T_INT)
            ->Identifier("Servie")
            ->InList("lines")
            ->Name("Shipped Qty")
            ->MicroData("http://schema.org/QuantitativeValue", "status")
            ->Group("Products")
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     */
    protected function setItemsFields($fieldName, $fieldData): void
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
        // Init Articles List
        $this->object->Articles = array();
        //====================================================================//
        // Verify Lines List & Update if Needed
        foreach ($fieldData as $product) {
            //====================================================================//
            // Safety Checks
            if (!self::validateItem($product)) {
                continue;
            }
            //====================================================================//
            // Decode Product Id
            $productId = self::objects()->id($product["ID"]);
            if (!$productId) {
                Splash::log()->warTrace("Invalid order Items SKU received");

                continue;
            }
            //====================================================================//
            // Search for This Items in Products List
            $articleIndex = $this->searchItem($productId);
            if (null !== $articleIndex) {
                $this->object->Articles[$articleIndex]["Quantite"] += (int) $product["Quantite"];

                continue;
            }
            //====================================================================//
            // Add Product Line to List
            $this->object->Articles[] = array(
                "ID" => $productId,
                "Quantite" => (int) $product["Quantite"],
            );
        }

        unset($this->in[$fieldName]);
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    protected function getItemsFields($key, $fieldName): void
    {
        //====================================================================//
        // Check if List field & Init List Array
        $fieldId = self::lists()->InitOutput($this->out, "lines", $fieldName);
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
                    $value = self::objects()->encode("Product", $product->{$fieldId});

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
            self::lists()->Insert($this->out, "lines", $fieldName, $index, $value);
        }

        unset($this->in[$key]);
    }

    /**
     * Validate Order Item Data
     *
     * @param array $product Order Item data
     *
     * @return bool
     */
    private static function validateItem(array $product): bool
    {
        //====================================================================//
        // Safety Checks
        if (!isset($product["ID"]) || !isset($product["Quantite"])) {
            Splash::log()->deb("Incomplete Order Items Line received");

            return false;
        }
        if (empty($product["ID"]) || empty($product["Quantite"])) {
            Splash::log()->deb("Incomplete Order Items Line received");

            return false;
        }

        return true;
    }

    /**
     * Serach for Order Item in Articles
     *
     * @param string $productId Product SKU
     *
     * @return null|int
     */
    private function searchItem(string $productId): ?int
    {
        //====================================================================//
        // Safety Checks - Articles List if Empty
        if (!is_array($this->object->Articles)) {
            return null;
        }
        //====================================================================//
        // Walk on Articles List
        foreach ($this->object->Articles as $index => $item) {
            //====================================================================//
            // Same Articles SKU
            if ($item["ID"] == $productId) {
                return (int) $index;
            }
        }

        return null;
    }
}
