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

/**
 * Access to Product Stock Location Fields
 */
trait LocationTrait
{
    protected static $knownStocks = array(
        "VETCO" => "VET: Stock Propriétaire",
        "CONSIGNE" => "VET: Stock Consigné",
    );

    /**
     * Build Fields using FieldFactory
     */
    protected function buildLocationFields()
    {
        $groupName = "Stocks";

        //====================================================================//
        // PRODUCT STOCKS
        //====================================================================//

        //====================================================================//
        // Stock Location
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("Stock")
            ->Group($groupName)
            ->Name("Stock")
            ->description("Nom du stock de l’article")
            ->MicroData("http://schema.org/Offer", "inventoryLocation")
            ->addChoice("", "Stock par défaut")
            ->addChoices(static::$knownStocks)
            ->isListed()
            ->isNotTested();

        //====================================================================//
        // Internal Optilog Stock Location
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("Gamme")
            ->Group($groupName)
            ->Name("Gamme")
            ->description("Nom de la Gamme de l’article (Interne)")
            ->MicroData("http://schema.org/Offer", "inventoryCategory")
            ->isReadOnly()
            ->isNotTested();
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    protected function getLocationFields($key, $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            //====================================================================//
            // Nom du stock de l’article
            case 'Stock':
                 $this->getSimple('Stock');

                break;
            //====================================================================//
            // Nom de la Gamme de l’article
            case 'Gamme':
                 $this->getSimple('Gamme');

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     */
    protected function setLocationFields($fieldName, $fieldData)
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'Stock':
                //====================================================================//
                // No Empty Values Allowed
                if (empty($fieldData)) {
                    unset($this->object->Stock);

                    break;
                }
                //====================================================================//
                // New Value => Erase Gamme (Setuped by Optilog)
                if (!empty($fieldData && ($fieldData != $this->object->Stock))) {
                    unset($this->object->Gamme);
                }
                //====================================================================//
                // Write new Value
                $this->setSimple($fieldName, $fieldData);

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }

    /**
     * Detect Stock Location for Creation using Inputs & Default Configuration
     *
     * @return null|string Stock Name to Write
     */
    protected function getNewStockLocation(): ?string
    {
        //====================================================================//
        // Check If A Stock Name is Given
        if (isset($this->in["Stock"]) && !empty($this->in["Stock"]) && is_scalar($this->in["Stock"])) {
            return (string) $this->in["Stock"];
        }
        //====================================================================//
        // Check if a Default Stock is Configured
        $dfStock = $this->connector->getParameter("dfStock");
        if (!empty($dfStock) && is_scalar($dfStock)) {
            return (string) $dfStock;
        }

        return null;
    }
}
