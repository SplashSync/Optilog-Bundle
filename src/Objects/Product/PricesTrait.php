<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2021 Splash Sync  <www.splashsync.com>
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
 * Products Prices Fields
 */
trait PricesTrait
{
    /**
     * Build Fields using FieldFactory
     */
    protected function buildPricesFields(): void
    {
        //====================================================================//
        // Product Selling Price
        $this->fieldsFactory()->create(SPL_T_PRICE)
            ->identifier("PV")
            ->name("Prix de vente")
            ->microData("http://schema.org/Product", "price")
            ->setPreferNone()
            ->isWriteOnly()
        ;
        //====================================================================//
        // WholeSale Price
        $this->fieldsFactory()->create(SPL_T_PRICE)
            ->identifier("PA")
            ->name("Prix d’achat")
            ->microData("http://schema.org/Product", "wholesalePrice")
            ->setPreferNone()
            ->isWriteOnly()
        ;
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param array  $fieldData Field Data
     */
    protected function setPricesFields(string $fieldName, array $fieldData): void
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
                // On n'envoie pas de Valeurs Nulles
                if (empty($newPrice)) {
                    unset($this->object->{$fieldName});

                    break;
                }
                $this->setSimple($fieldName, $newPrice);

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }
}
