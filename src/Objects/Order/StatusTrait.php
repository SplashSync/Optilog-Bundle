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

use Splash\Connectors\Optilog\Models\RestHelper;
use Splash\Connectors\Optilog\Models\StatusHelper;
use Splash\Core\SplashCore      as Splash;

/**
 * Access to Orders Status Fields
 */
trait StatusTrait
{
    /**
     * Build Fields using FieldFactory
     */
    protected function buildStatusFields(): void
    {
        //====================================================================//
        // ORDER STATUS
        //====================================================================//

        //====================================================================//
        // Order Current Status
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("Statut")
            ->Name("Order status")
            ->Description("Status of the order")
            ->MicroData("http://schema.org/Order", "orderStatus")
            ->addChoices(StatusHelper::getAllNames())
            ->isListed()
            ->isReadOnly();

        //====================================================================//
        // Order Real Status as Int
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("StatutRaw")
            ->Name("Order status Raw")
            ->Description("Raw Optilog Status of the order")
            ->MicroData("http://schema.org/Order", "orderStatusCode")
            ->isReadOnly()
        ;

        //====================================================================//
        // ORDER STATUS FLAGS
        //====================================================================//

        //====================================================================//
        // Is Validated
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->Identifier("Mode")
            ->Name("Is Valid")
            ->MicroData("http://schema.org/OrderStatus", "OrderProcessing")
            ->setPreferWrite()
            ->isLogged()
        ;
        //====================================================================//
        // Is To Ship
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->Identifier("isToShip")
            ->Name("Is To Ship")
            ->MicroData("http://schema.org/OrderStatus", "OrderToShip")
            ->isWriteOnly()
        ;
        //====================================================================//
        // Is Canceled
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->Identifier("isCanceled")
            ->Name("Is Canceled")
            ->MicroData("http://schema.org/OrderStatus", "OrderCancelled")
            ->setPreferWrite()
            ->isLogged()
        ;
        //====================================================================//
        // Is To Delete
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->Identifier("isToDelete")
            ->Name("Is To Delete")
            ->MicroData("http://schema.org/OrderStatus", "OrderToDelete")
            ->isWriteOnly()
        ;
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    protected function getStatusFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'Mode':
                $this->out[$fieldName] = $this->isValidStatus();

                break;
            case 'isCanceled':
                $this->out[$fieldName] = $this->isCanceledStatus();

                break;
            case 'Statut':
                if ($this->isAllowedStatusUpdates()) {
                    $this->out[$fieldName] = $this->getSplashStatus();
                }

                break;
            case 'StatutRaw':
                $statut = $this->getOptilogStatus();
                $this->out[$fieldName] = (string) $statut." | ".StatusHelper::getName($statut);

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
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function setStatusFields(string $fieldName, $fieldData): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'Mode':
                //====================================================================//
                // VALIDATE ORDER IF ALLOWED
                if (!empty($fieldData) && ($this->isAllowedValidate())) {
                    $this->object->Mode = "VALIDATE";
                    $this->needUpdate();
                }

                break;
            case 'isCanceled':
                //====================================================================//
                // CANCEL ORDER IF ALLOWED
                if (!empty($fieldData) && ($this->isAllowedCancel())) {
                    $this->object->Mode = "UNVALIDATE";
                    $this->needUpdate();
                }

                break;
            case 'isToShip':
                //====================================================================//
                // SHIP ORDER IF ALLOWED
                if (!empty($fieldData)) {
                    if (in_array($this->getOptilogStatus(), array(-4, 3), true)) {
                        $this->object->Mode = "EXP_GO";
                        $this->needUpdate();
                    }
                }

                break;
            case 'isToDelete':
                if (!empty($fieldData)) {
                    $this->object->Mode = "DELETE";
                    $this->needUpdate();
                }

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }

    /**
     * Read Order Status
     *
     * @return int
     */
    private function getOptilogStatus(): int
    {
        //====================================================================//
        // Debug => Force Order Status
        if ($this->connector->isDebugMode() && $this->getParameter($this->object->DestID, false, 'ForcedStatus')) {
            return (int) $this->getParameter($this->object->DestID, false, 'ForcedStatus');
        }

        return RestHelper::isApiV2Mode()
            ? (int) $this->object->IdStatut
            : (int) $this->object->Statut
        ;
    }

    /**
     * Read Order Status
     *
     * @return string
     */
    private function getSplashStatus()
    {
        return (string) StatusHelper::toSplash($this->getOptilogStatus());
    }

    /**
     * Check if Order Status Updates are Allowed
     *
     * @return bool
     */
    private function isAllowedStatusUpdates(): bool
    {
        //====================================================================//
        // Debug => Always Allow Order Status Updates
        if ($this->connector->isDebugMode()) {
            return true;
        }
        //====================================================================//
        // If Order NOT Validated => No Status Updated
        if (0 == $this->getOptilogStatus()) {
            return false;
        }

        return true;
    }

    /**
     * Check if Order Status Validation is Allowed
     *
     * @return bool
     */
    private function isAllowedValidate(): bool
    {
        //====================================================================//
        // Debug => Force Order Status
        if ($this->connector->isDebugMode()) {
            Splash::log()->war("Order Validation is disabled in Preproduction.");

            return false;
        }
        //====================================================================//
        // If Order NOT Validated Yet
        if ($this->getOptilogStatus() > 0) {
            return false;
        }

        return true;
    }

    /**
     * Check if Order Status Cancellation is Allowed
     *
     * @return bool
     */
    private function isAllowedCancel(): bool
    {
        //====================================================================//
        // If Order NOT Validated Yet => Stay in ALTER Mode
        if ($this->getOptilogStatus() <= 0) {
            return false;
        }

        return true;
    }

    /**
     * Check if Order Status is Valid Order
     *
     * @return bool
     */
    private function isValidStatus(): bool
    {
        //====================================================================//
        // If Order NOT Validated Yet
        if ($this->getOptilogStatus() <= 0) {
            return false;
        }
        //====================================================================//
        // If Order Returned
        if (in_array($this->getOptilogStatus(), array(10), true)) {
            return false;
        }

        return true;
    }

    /**
     * Check if Order Status is Canceled Order
     *
     * @return bool
     */
    private function isCanceledStatus(): bool
    {
        //====================================================================//
        // If Order Canceled or Returned
        if ($this->getOptilogStatus() <= 0) {
            return true;
        }

        return false;
    }
}
