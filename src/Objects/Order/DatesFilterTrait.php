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

use DateTime;
use Splash\Core\SplashCore as Splash;

/**
 * Filter Order Creation by Created Dates
 */
trait DatesFilterTrait
{
    /**
     * Build Core Fields using FieldFactory
     */
    protected function buildFilterFields(): void
    {
        //====================================================================//
        // Creation Date
        $this->fieldsFactory()->create(SPL_T_DATETIME)
            ->identifier("createdAt")
            ->name("Date Created")
            ->description("Order Creation Date: Only used to Filter Orders for Optilog")
            ->microData("http://schema.org/DataFeedItem", "dateCreated")
            ->isRequired()
            ->isWriteOnly()
        ;
    }

    /**
     * Check if this Order is Allowed Writing on Optilog
     *
     * @return bool
     */
    protected function isAllowedDate(): bool
    {
        //====================================================================//
        // Check If Min Order Date was Set up
        $minOrderDate = $this->connector->getParameter("minOrderDate");
        if (!($minOrderDate instanceof DateTime)) {
            return true;
        }
        //====================================================================//
        // Check If Received Order Date is Given
        if (!isset($this->in["createdAt"]) || empty($this->in["createdAt"]) || !is_scalar($this->in["createdAt"])) {
            return false;
        }
        //====================================================================//
        // Convert Received Order date to Datetime
        $receivedOrderDate = new DateTime((string) $this->in["createdAt"]);

        //====================================================================//
        // Check if Received date is After Set up Date
        return ($receivedOrderDate > $minOrderDate);
    }

    /**
     * Mark Order as Filtered & Return Details in Log
     *
     * @return false
     */
    protected function logFilteredOrder(): bool
    {
        Splash::log()->war("This Order is Filtered by Optilog Connector.");
        //====================================================================//
        // Check If Min Order Date was Set up
        $minOrderDate = $this->connector->getParameter("minOrderDate");
        if ($minOrderDate instanceof DateTime) {
            Splash::log()->war("Minimum Order Date: ".$minOrderDate->format(SPL_T_DATETIMECAST));
        }
        //====================================================================//
        // Check If Received Order Date is Given
        if (isset($this->in["createdAt"]) && is_scalar($this->in["createdAt"])) {
            Splash::log()->war("Received Order Date: ".$this->in["createdAt"]);
        }

        return false;
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function setFilterFields($fieldName, $fieldData): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'createdAt':
                // THIS FIELD IS NOT USED
                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }
}
