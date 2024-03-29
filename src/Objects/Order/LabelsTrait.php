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

namespace Splash\Connectors\Optilog\Objects\Order;

/**
 * Access to Order Labels Fields
 */
trait LabelsTrait
{
    /**
     * Build Labels Fields using FieldFactory
     */
    protected function buildLabelsFields(): void
    {
        //====================================================================//
        // Label 1
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("Libelle1")
            ->Name("Ligne 1 de commentaire")
            ->isNotTested()
            ->isWriteOnly()
            ->microData("http://schema.org/ParcelDelivery", "alternateName")
        ;
        //====================================================================//
        // Label 2
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("Libelle2")
            ->Name("Ligne 2 de commentaire")
            ->isNotTested()
            ->isWriteOnly()
        ;
        //====================================================================//
        // Label 3
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("Libelle3")
            ->Name("Ligne 3 de commentaire")
            ->isNotTested()
            ->isWriteOnly()
        ;
        //====================================================================//
        // Label 4
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("Libelle4")
            ->Name("Ligne 4 de commentaire")
            ->isNotTested()
            ->isWriteOnly()
        ;
        //====================================================================//
        // Label 5
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("Libelle5")
            ->Name("Ligne 5 de commentaire")
            ->isNotTested()
            ->isWriteOnly()
        ;
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     */
    protected function setLabelsFields(string $fieldName, $fieldData): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'Libelle1':
            case 'Libelle2':
            case 'Libelle3':
            case 'Libelle4':
            case 'Libelle5':
                $this->setSimple($fieldName, $fieldData);

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }
}
