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
 * Access to Product Core Fields
 */
trait CoreTrait
{
    /**
     * New SKU if Modified
     *
     * @var null|string
     */
    protected $oldSKU;

    /**
     * Build Core Fields using FieldFactory
     */
    private function buildCoreFields()
    {
        //====================================================================//
        // Reference
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("sku")
            ->Name("Reference")
            ->isListed()
            ->MicroData("http://schema.org/Product", "model")
            ->isRequired();

        //====================================================================//
        // Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("Libelle")
            ->Name("Product Name with Options")
            ->MicroData("http://schema.org/Product", "name")
            ->isListed()
            ->isRequired();

        //====================================================================//
        // Active => Product Is available_for_order
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->Identifier("IsActif")
            ->Name("Etat de l’article")
            ->MicroData("http://schema.org/Product", "offered")
            ->isListed();
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    private function getCoreFields($key, $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            //====================================================================//
            // MAIN INFORMATIONS
            //====================================================================//
            case 'sku':
                $this->out[$fieldName] = $this->object->ID;

                break;
            case 'Libelle':
                $this->getSimple($fieldName);

                break;
            case 'IsActif':
                $this->getSimpleBool($fieldName);

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
    private function setCoreFields($fieldName, $fieldData)
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            //====================================================================//
            // MAIN INFORMATIONS
            //====================================================================//
            case 'sku':
                if ($this->object->ID == $fieldData) {
                    continue;
                }

                $this->setSimple("ID", $fieldData);
                $this->oldSKU = $fieldData;

                break;
            case 'Libelle':
            case 'IsActif':
                $this->setSimple($fieldName, $fieldData);

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }
}
