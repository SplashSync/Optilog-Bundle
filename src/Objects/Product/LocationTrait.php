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

/**
 * Access to Product Stock Location Fields
 */
trait LocationTrait
{
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
            ->isReadOnly()
            ->isNotTested();

        //====================================================================//
        // Stock Location 2
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("Gamme")
            ->Group($groupName)
            ->Name("Gamme")
            ->description("Nom de la Gamme de l’article")
            ->MicroData("http://schema.org/Offer", "inventoryCategory")
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
                $this->out[$fieldName] = $this->object->NomStock;
                // $this->getSimple('NomStock');

                break;
            //====================================================================//
            // Nom de la Gamme de l’article
            case 'Gamme':
                $this->out[$fieldName] = $this->object->NomGamme;
                // $this->getSimple('NomGamme');

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
            case 'Gamme':
                // On N'envoi pas de Valeurs Nulles
                if (empty($fieldData)) {
                    unset($this->object->{$fieldName});

                    continue;
                }
                $this->setSimple($fieldName, $fieldData);

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }
}
