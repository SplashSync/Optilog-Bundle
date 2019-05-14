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
use Splash\Components\UnitConverter as UNITS;

/**
 * Access to Product Main Fields
 */
trait MainTrait
{
    
    /**
     * Build Address Fields using FieldFactory
     */
    protected function buildMainFields()
    {
        $groupName = "Dimensions";

        //====================================================================//
        // PRODUCT SPECIFICATIONS
        //====================================================================//

        //====================================================================//
        // Weight
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->Identifier("Poids")
            ->Name("Poids d’une unité")
            ->description("Poids d’une unité en grammes (Kg >> g)")
            ->Group($groupName)
            ->MicroData("http://schema.org/Product", "weight")
            ->isRequired()
            ->isWriteOnly();

        //====================================================================//
        // Height
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->Identifier("Hauteur")
            ->Name("Hauteur")
            ->description("Hauteur en mm (M >> mm)")
            ->Group($groupName)
            ->MicroData("http://schema.org/Product", "height")
            ->isWriteOnly();

        //====================================================================//
        // Depth
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->Identifier("Longueur")
            ->Name("Longueur")
            ->description("Longueur en mm (M >> mm)")
            ->Group($groupName)
            ->MicroData("http://schema.org/Product", "depth")
            ->isWriteOnly();

        //====================================================================//
        // Width
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->Identifier("Largeur")
            ->Name("Largeur")
            ->description("Largeur en mm (M >> mm)")
            ->Group($groupName)
            ->MicroData("http://schema.org/Product", "width")
            ->isWriteOnly();

        //====================================================================//
        // PRODUCT BARCODES
        //====================================================================//

        //====================================================================//
        // EAN
        $this->fieldsFactory()->create(SPL_T_INT)
            ->Identifier("EAN")
            ->Name("EAN Code")
            ->description("Code EAN du produit unitaire")
            ->MicroData("http://schema.org/Product", "gtin13")
            ->isWriteOnly();
    }

    
    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     */
    protected function setMainFields($fieldName, $fieldData)
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            //====================================================================//
            // PRODUCT SPECIFICATIONS
            //====================================================================//
            case 'Poids':
                // On N'envoi pas de Valeurs Nulles
                if (empty($fieldData)) {
                    unset($this->object->$fieldName);
                    continue;
                }
                $this->setSimpleFloat($fieldName, UNITS::convertWeight($fieldData, UNITS::MASS_GRAM));

                break;
            case 'Hauteur':
            case 'Longueur':
            case 'Largeur':
                // On N'envoi pas de Valeurs Nulles
                if (empty($fieldData)) {
                    unset($this->object->$fieldName);
                    continue;
                }
                $this->setSimpleFloat($fieldName, UNITS::convertLength($fieldData, UNITS::LENGTH_MM));

                break;
            
            //====================================================================//
            // PRODUCT BARCODES
            //====================================================================//
            case 'EAN':
                // On N'envoi pas de Valeurs Nulles
                if (empty($fieldData)) {
                    unset($this->object->$fieldName);
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
