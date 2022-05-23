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

namespace Splash\Connectors\Optilog\Objects\Product;

/**
 * Access to Product Stock Fields
 */
trait StockTrait
{
    /**
     * Build Fields using FieldFactory
     */
    protected function buildStockFields(): void
    {
        $groupName = "Stocks";

        //====================================================================//
        // PRODUCT STOCKS
        //====================================================================//

        //====================================================================//
        // Stock Reel
        $this->fieldsFactory()->create(SPL_T_INT)
            ->Identifier("Stk_Dispo")
            ->Name("Stock Disponible")
            ->description("Stocks Available for Orders")
            ->MicroData("http://schema.org/Offer", "inventoryLevel")
            ->Group($groupName)
            ->isReadOnly()
            ->isListed();

        //====================================================================//
        // Stock Physique
        $this->fieldsFactory()->create(SPL_T_INT)
            ->Identifier("Stk_Physique")
            ->Name("Stock Physique")
            ->description("Current Total Inventory Level")
            ->Group($groupName)
            ->isReadOnly()
            ->isListed();

        //====================================================================//
        // Stock Commande
        $this->fieldsFactory()->create(SPL_T_INT)
            ->Identifier("Stk_Commande")
            ->Name("Stock Commande")
            ->description("Reserved for Waiting Orders")
            ->Group($groupName)
            ->isReadOnly();

        //====================================================================//
        // Out of Stock Flag
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->Identifier("outofstock")
            ->Name("This product is out of stock")
            ->MicroData("http://schema.org/ItemAvailability", "OutOfStock")
            ->Group($groupName)
            ->isReadOnly();
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    protected function getStockFields($key, $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            //====================================================================//
            // PRODUCT STOCKS
            //====================================================================//
            // Stock Reel
            case 'Stk_Dispo':
                //====================================================================//
                // Debug => Random Stocks
                if ($this->connector->isDebugMode() && $this->getParameter($this->object->ID, false, 'RandomStocks')) {
                    $this->object->Stk_Dispo = rand(10, 100);
                }
                $this->getSimple($fieldName);

                break;
            case 'Stk_Physique':
            case 'Stk_Commande':
                $this->getSimple($fieldName);

                break;
            //====================================================================//
            // Out Of Stock
            case 'outofstock':
                $this->out[$fieldName] = ($this->object->Stk_Dispo > 0) ? false : true;

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }
}
