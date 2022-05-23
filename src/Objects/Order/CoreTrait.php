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
            ->identifier("IntID")
            ->name("Internal ID")
            ->isListed()
            ->isNotTested()
            ->isReadOnly()
        ;
        //====================================================================//
        // Internal Reference
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("DestID")
            ->name("Reference")
            ->isListed()
            ->microData("http://schema.org/Order", "orderNumber")
            ->isRequired()
            ->isNotTested()
        ;
        //====================================================================//
        // Comment
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("Commentaire")
            ->name("Commentaire")
            ->isReadOnly()
        ;
        //====================================================================//
        // ID Operation
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("Operation")
            ->name("ID Operation")
            ->isNotTested()
            ->isWriteOnly()
            ->microData("http://schema.org/Order", "disambiguatingDescription")
        ;
        //====================================================================//
        // Order Origin
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("Origin")
            ->name("Order Origin")
            ->description("Order Source Website. Used to filter Orders")
            ->isNotTested()
            ->isWriteOnly()
            ->microData("http://splashync.com/schemas", "SourceNodeName")
            ->setPreferNone()
        ;
        //====================================================================//
        // Order Expected Delivery Date
        $this->fieldsFactory()->create(SPL_T_DATE)
            ->identifier("DIL")
            ->name("Delivery Date")
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
    protected function getCoreFields(string $key, string $fieldName): void
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
    protected function setCoreFields(string $fieldName, $fieldData): void
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
            case 'Origin':
                // NOTHING TO DO

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
     * @param null|string  $fieldData Field Data
     */
    protected function setMetaFields(string $fieldName, ?string $fieldData): void
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
     * Check if this Order is Allowed Writing on Optilog
     *
     * @return bool
     */
    protected function isAllowedOrigin(): bool
    {
        //====================================================================//
        // Check if Origins are Selected
        $knownOrigins = $this->connector->getParameter("OrderOrigins");
        if (!is_array($knownOrigins) || empty($knownOrigins)) {
            return true;
        }
        //====================================================================//
        // Check If Received Order Origin Name
        if (empty($this->in["Origin"]) || !is_scalar($this->in["Origin"])) {
            return true;
        }
        //====================================================================//
        // Identify Origin by Name
        if (isset($knownOrigins[trim((string) $this->in["Origin"])])) {
            if ("REJECTED" == $knownOrigins[trim((string) $this->in["Origin"])]) {
                Splash::log()->war("Rejected Origin Detected...");

                return false;
            }
        }

        return true;
    }

    /**
     * Convert Splash Date to Optilog DIL Date
     *
     * @param null|string $fieldData Field Data
     *
     * @return null|string
     */
    private static function toOptilogDIL(?string $fieldData): ?string
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
