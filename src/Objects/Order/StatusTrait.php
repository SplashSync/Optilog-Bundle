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

namespace Splash\Connectors\Optilog\Objects\Order;

use Splash\Connectors\Optilog\Models\StatusCodes;

/**
 * Access to Orders Status Fields
 */
trait StatusTrait
{

    /**
     * Build Fields using FieldFactory
     */
    protected function buildStatusFields()
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
        // ORDER STATUS FLAGS
        //====================================================================//

        //====================================================================//
        // Is Validated
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->Identifier("Mode")
            ->Name("Is Valid")
            ->MicroData("http://schema.org/OrderStatus", "OrderProcessing")
            ->isWriteOnly();

    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    protected function getStatusFields($key, $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            
            case 'Statut':
                $this->out[$fieldName] = $this->getSplashStatus();

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
    protected function setStatusFields($fieldName, $fieldData)
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'Mode':
                //====================================================================//
                // TWO POSSIBLES INPUTS MODES
                // => Order is Draft >> ALTER
                // => Order is Validated >> VALIDATE
                $this->object->Mode = $fieldData ? "VALIDATE" : "ALTER";

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
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function getSplashStatus()
    {
        //====================================================================//
        // If order is in  Static Status => Use Static Status
        if (isset(StatusCodes::SPLASH[$this->object->Statut])) {
            return StatusCodes::SPLASH[$this->object->Statut];
        }
        //====================================================================//
        // Default Status => Order is Closed & Delivered
        return "OrderDelivered";
    }
}
