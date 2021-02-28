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

namespace Splash\Connectors\Optilog\Objects\Order;

use DateTime;
use Splash\Client\Splash;

/**
 * Access to Order Core Fields
 */
trait CoreTrait
{
    /**
     * Build Core Fields using FieldFactory
     */
    protected function buildCoreFields(): void
    {
        //====================================================================//
        // Internal Reference
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("IntID")
            ->Name("Internal ID")
            ->isListed()
            ->isNotTested()
            ->isReadOnly();

        //====================================================================//
        // Internal Reference
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("DestID")
            ->Name("Reference")
            ->isListed()
            ->MicroData("http://schema.org/Order", "orderNumber")
            ->isRequired()
            ->isNotTested();

        //====================================================================//
        // Comment
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("Commentaire")
            ->Name("Commentaire")
            ->isReadOnly();

        //====================================================================//
        // ID Operation
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("Operation")
            ->Name("ID Operation")
            ->isNotTested()
            ->isWriteOnly()
            ->microData("http://schema.org/Order", "disambiguatingDescription")
        ;

        //====================================================================//
        // Order Expected Delivery Date
        $this->fieldsFactory()->create(SPL_T_DATE)
            ->Identifier("DIL")
            ->Name("Delivery Date")
            ->isNotTested()
            ->isWriteOnly()
            ->microData("http://schema.org/ParcelDelivery", "expectedArrivalUntil")
            ->setPreferNone()
        ;
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    protected function getCoreFields($key, $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'IntID':
                $this->out[$fieldName] = $this->object->ID;

                break;
            case 'DestID':
            case 'Commentaire':
                $this->getSimple($fieldName);

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
    protected function setCoreFields($fieldName, $fieldData): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'DestID':
                //====================================================================//
                // Detect Rejected Order Id => No Update Allowed
                if ($this->isRejectedId($this->object->DestID)) {
                    break;
                }
                $this->setSimple($fieldName, $fieldData);

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     */
    protected function setMetaFields(string $fieldName, $fieldData): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'Operation':
                if (!empty($fieldData)) {
                    $this->setSimple($fieldName, $fieldData);
                }

                break;
            case 'DIL':
                if (!empty($fieldData)) {
                    //====================================================================//
                    // Convert DIL to Optilog Format
                    $orderDil = self::toOptilogDIL($fieldData);
                    if ($orderDil) {
                        $this->setSimple($fieldName, $orderDil);
                    }
                }

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }

    /**
     * Convert Splash Date to Optilog DIL Date
     *
     * @param mixed $fieldData Field Data
     *
     * @return null|string
     */
    private static function toOptilogDIL($fieldData): ?string
    {
        if (!empty($fieldData)) {
            try {
                $datetime = new DateTime($fieldData);

                return $datetime->format("d/m/Y");
            } catch (\Exception $exc) {
                Splash::log()->err("Malformed DIL Received ");

                return null;
            }
        }

        return null;
    }
}
