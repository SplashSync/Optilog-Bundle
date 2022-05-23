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

use Splash\Components\UnitConverter as UNITS;

/**
 * Access to Product Main Fields
 */
trait MainTrait
{
    /**
     * Build Address Fields using FieldFactory
     */
    protected function buildMainFields(): void
    {
        $groupName = "Dimensions";

        //====================================================================//
        // PRODUCT SPECIFICATIONS
        //====================================================================//

        //====================================================================//
        // Weight
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->identifier("Poids")
            ->name("Poids d’une unité")
            ->description("Poids d’une unité en grammes (Kg >> g)")
            ->group($groupName)
            ->microData("http://schema.org/Product", "weight")
            ->setPreferNone()
            ->isWriteOnly()
        ;
        //====================================================================//
        // Height
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->identifier("Hauteur")
            ->name("Hauteur")
            ->description("Hauteur en mm (M >> mm)")
            ->group($groupName)
            ->microData("http://schema.org/Product", "height")
            ->setPreferNone()
            ->isWriteOnly()
        ;
        //====================================================================//
        // Depth
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->identifier("Longueur")
            ->name("Longueur")
            ->description("Longueur en mm (M >> mm)")
            ->group($groupName)
            ->microData("http://schema.org/Product", "depth")
            ->setPreferNone()
            ->isWriteOnly()
        ;
        //====================================================================//
        // Width
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->identifier("Largeur")
            ->name("Largeur")
            ->description("Largeur en mm (M >> mm)")
            ->group($groupName)
            ->microData("http://schema.org/Product", "width")
            ->setPreferNone()
            ->isWriteOnly()
        ;
        //====================================================================//
        // PRODUCT BARCODES
        //====================================================================//

        //====================================================================//
        // EAN
        $this->fieldsFactory()->create(SPL_T_INT)
            ->identifier("EAN")
            ->name("EAN Code")
            ->description("Code EAN du produit unitaire")
            ->microData("http://schema.org/Product", "gtin13")
            ->isWriteOnly()
        ;
    }

    /**
     * Write Given Fields
     *
     * @param string                $fieldName Field Identifier / Name
     * @param null|float|int|string $fieldData Field Data
     */
    protected function setMainFields(string $fieldName, null|int|float|string $fieldData): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            //====================================================================//
            // PRODUCT SPECIFICATIONS
            //====================================================================//
            case 'Poids':
                // On n'envoie pas de valeurs nulles
                if (empty($fieldData)) {
                    unset($this->object->{$fieldName});

                    break;
                }
                $this->setSimpleFloat($fieldName, UNITS::convertWeight((float) $fieldData, UNITS::MASS_GRAM));

                break;
            case 'Hauteur':
            case 'Longueur':
            case 'Largeur':
                // On n'envoie pas de valeurs nulles
                if (empty($fieldData)) {
                    unset($this->object->{$fieldName});

                    break;
                }
                $this->setSimpleFloat($fieldName, UNITS::convertLength((float) $fieldData, UNITS::LENGTH_MM));

                break;
            //====================================================================//
            // PRODUCT BARCODES
            //====================================================================//
            case 'EAN':
                // On n'envoie pas de valeurs nulles
                if (empty($fieldData)) {
                    unset($this->object->{$fieldName});

                    break;
                }
                $this->setSimple($fieldName, $fieldData);

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }
}
