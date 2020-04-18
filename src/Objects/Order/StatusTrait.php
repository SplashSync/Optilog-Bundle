<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2020 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\Optilog\Objects\Order;

use Splash\Connectors\Optilog\Models\StatusCodes;
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
            ->addChoices(StatusCodes::SPLASH)
            ->isListed()
            ->isReadOnly();

        //====================================================================//
        // Order Real Status as Int
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("StatutRaw")
            ->Name("Order status Raw")
            ->Description("Raw Optilog Status of the order")
            ->MicroData("http://schema.org/Order", "orderStatusCode")
            ->isReadOnly();

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
            ->isLogged();

        //====================================================================//
        // Is Canceled
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->Identifier("isCanceled")
            ->Name("Is Canceled")
            ->MicroData("http://schema.org/OrderStatus", "OrderCancelled")
            ->setPreferWrite()
            ->isLogged();
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    protected function getStatusFields($key, $fieldName): void
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
                $this->out[$fieldName] = (string) $this->object->Statut;
                //====================================================================//
                // If order is in  Static Status => Use Static Status
                if (isset(StatusCodes::NAMES[$this->object->Statut])) {
                    $this->out[$fieldName] .= " | ".StatusCodes::NAMES[$this->object->Statut];
                }

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
    protected function setStatusFields($fieldName, $fieldData): void
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
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }

    /**
     * Read Order Status
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function getSplashStatus()
    {
        //====================================================================//
        // Debug => Force Order Status
        if ($this->connector->isDebugMode() && $this->getParameter($this->object->DestID, false, 'ForcedStatus')) {
            $this->object->Statut = $this->getParameter($this->object->DestID, false, 'ForcedStatus');
        }
        //====================================================================//
        // If order is in  Static Status => Use Static Status
        if (isset(StatusCodes::SPLASH[$this->object->Statut])) {
            return StatusCodes::SPLASH[$this->object->Statut];
        }
        //====================================================================//
        // Unknown Status => No Order Status Update
        return "";
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
        if (0 == $this->object->Statut) {
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
        if ($this->object->Statut > 0) {
            return false;
        }

        return true;
    }

    /**
     * Check if Order Status Cancelation is Allowed
     *
     * @return bool
     */
    private function isAllowedCancel(): bool
    {
        //====================================================================//
        // If Order NOT Validated Yet => Stay in ALTER Mode
        if ($this->object->Statut <= 0) {
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
        if ($this->object->Statut <= 0) {
            return false;
        }
        //====================================================================//
        // If Order Returned
        if (in_array($this->object->Statut, array(10), true)) {
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
        // If Order Cancled or Returned
        if ($this->object->Statut <= 0) {
            return true;
        }

        return false;
    }
}
