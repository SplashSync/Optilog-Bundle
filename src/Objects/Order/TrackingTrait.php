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

namespace   Splash\Connectors\Optilog\Objects\Order;

use Splash\Connectors\Optilog\Models\CarrierCodes;
use Splash\Core\SplashCore      as Splash;

/**
 * Opilog Orders Tracking Fields Access
 */
trait TrackingTrait
{
    /**
     * Build Fields using FieldFactory
     */
    protected function buildTrackingFields()
    {
        //====================================================================//
        // Order Shipping Method
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("Transporteur")
            ->Name("Code Transporteur")
            ->description(
                "Les associataions entre codes Code Optilog et "
                ."Codes Transporteur Splash sont disponible dans la configuration du Connecteur."
            )
            ->MicroData("http://schema.org/ParcelDelivery", "identifier")
            ->group("Tracking")
            ->addChoices($this->getUserCarrierNames())
            ->isWriteOnly();

        //====================================================================//
        // Order Tracking Number
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("Bordereau")
            ->Name("Tracking Number")
            ->MicroData("http://schema.org/ParcelDelivery", "trackingNumber")
            ->group("Tracking")
            ->isListed()
            ->isReadOnly();

        //====================================================================//
        // Order Tracking Url
        $this->fieldsFactory()->Create(SPL_T_URL)
            ->Identifier("URL")
            ->Name("Tracking Url")
            ->MicroData("http://schema.org/ParcelDelivery", "trackingUrl")
            ->group("Tracking")
            ->isReadOnly();
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    protected function getTrackingFields($key, $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'Bordereau':
            case 'URL':
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
    protected function setTrackingFields($fieldName, $fieldData)
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'Transporteur':
                //====================================================================//
                // Detect Carrier Code
                $carrierCode = $this->getCarrierCode((string) $fieldData);
                if (!$carrierCode) {
                    Splash::log()->war("Unable to detect Carrier Code!!");

                    break;
                }
                //====================================================================//
                // Update Order Carrier Code
                $this->setSimple($fieldName, $carrierCode);

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }

    /**
     * Get Optilog Carriers Code
     *
     * @param string $carrierName
     *
     * @return null|string
     */
    private function getCarrierCode(string $carrierName): ?string
    {
        //====================================================================//
        // Check Carrier Name is Not Empty
        if (empty($carrierName)) {
            return null;
        }
        //====================================================================//
        // Load List from Connector Parameters
        $carriers = $this->getParameter("Carriers", array());

        //====================================================================//
        // Identify Carrier Code from Name
        $carrierCode = $carrierName;
        if (is_array($carriers) && isset($carriers[$carrierName])) {
            $carrierCode = $carriers[$carrierName];
        }
        //====================================================================//
        // Check Carrier Code is Valid
        if (!in_array($carrierCode, array_keys(CarrierCodes::CODES), true)) {
            if (is_array($carriers) && !isset($carriers[$carrierName])) {
                Splash::log()->war("Unable to Detect Optilog Carrier Code, Given : ".$carrierName);
                Splash::log()->www("Configured Names", $carriers);
            }

            return null;
        }
        //====================================================================//
        // Return Carrier Code
        return $carrierCode;
    }

    /**
     * Get User Carriers Names
     *
     * @return array
     */
    private function getUserCarrierNames(): array
    {
        //====================================================================//
        // Load List from Connector Parameters
        $carriers = $this->getParameter("Carriers", array());
        //====================================================================//
        // Safety Check
        if (!is_array($carriers)) {
            return CarrierCodes::CODES;
        }
        //====================================================================//
        // Return Carrier Names
        return array_flip($carriers);
    }
}
