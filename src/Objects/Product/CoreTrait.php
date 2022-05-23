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
 * Access to Product Core Fields
 */
trait CoreTrait
{
    /**
     * New SKU if Modified
     *
     * @var null|string
     */
    protected ?string $oldSKU = null;

    /**
     * Build Core Fields using FieldFactory
     */
    private function buildCoreFields(): void
    {
        //====================================================================//
        // Reference
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("sku")
            ->name("Reference")
            ->isListed()
            ->microData("http://schema.org/Product", "model")
            ->isRequired()
            ->isNotTested()
        ;
        //====================================================================//
        // Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("Libelle")
            ->name("Product Name with Options")
            ->microData("http://schema.org/Product", "name")
            ->isListed()
            ->isRequired()
        ;
        //====================================================================//
        // Description (For Standards Only)
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("Description")
            ->name("Product Name with Options")
            ->microData("http://schema.org/Product", "description")
            ->isReadOnly()
            ->setPreferNone()
        ;
        //====================================================================//
        // Référence Fournisseur
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("IdFournisseur")
            ->name("Manufacturer Part Number")
            ->microData("http://schema.org/Product", "mpn")
            ->isWriteOnly()
        ;
        //====================================================================//
        // Active => Product Is available_for_order
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("IsActif")
            ->name("Etat de l’article")
            ->microData("http://schema.org/Product", "offered")
            ->isListed()
        ;
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    private function getCoreFields(string $key, string $fieldName): void
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
            case 'Description':
                $this->out[$fieldName] = $this->object->Libelle;

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
     * @param bool|string|null $fieldData Field Data
     */
    private function setCoreFields(string $fieldName, bool|string|null $fieldData): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            //====================================================================//
            // MAIN INFORMATIONS
            //====================================================================//
            case 'sku':
                $newSku = trim((string) $fieldData);
                if ($this->object->ID == $newSku) {
                    break;
                }

                $this->oldSKU = $this->object->ID;
                $this->setSimple("ID", $newSku);

                break;
            case 'Libelle':
            case 'IdFournisseur':
                $this->setSimple($fieldName, $fieldData);

                break;
            case 'IsActif':
                $this->setSimple($fieldName, (bool) $fieldData);

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }
}
