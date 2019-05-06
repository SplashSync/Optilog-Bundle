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

namespace Splash\Connectors\Optilog\Objects\Product;

use Splash\Core\SplashCore      as Splash;
use Splash\Local\Local;

/**
 * Products Prices Fields
 */
trait PricesTrait
{
    
    /**
     * Build Fields using FieldFactory
     */
    protected function buildPricesFields()
    {
        global $conf,$langs;

        //====================================================================//
        // Product Selling Price
        $this->fieldsFactory()->create(SPL_T_PRICE)
            ->Identifier("PV")
            ->Name("Prix de vente")
            ->MicroData("http://schema.org/Product", "price")
            ->isWriteOnly();

        //====================================================================//
        // WholeSale Price
        $this->fieldsFactory()->create(SPL_T_PRICE)
            ->Identifier("PA")
            ->Name("Prix d’achat")
            ->MicroData("http://schema.org/Product", "wholesalePrice")
            ->isWriteOnly();
    }
    
    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     */
    protected function setPricesFields($fieldName, $fieldData)
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            //====================================================================//
            // PRODUCT PRICES
            //====================================================================//
            case 'PV':
            case 'PA':
                $newPrice = self::prices()->taxExcluded($fieldData);
                // On N'envoi pas de Valeurs Nulles
                if(empty($newPrice)) {
                    unset($this->object->$fieldName);
                    continue;
                }
                $this->setSimple($fieldName, $newPrice);

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }
}