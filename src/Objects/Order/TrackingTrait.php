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
     * @var null|float
     */
    private $totalPrice;

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

        //====================================================================//
        // Order Total Price TTC
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->Identifier("total")
            ->Name("Total (Tax incl.)")
            ->MicroData("http://schema.org/Invoice", "totalPaymentDueTaxIncluded")
            ->isWriteOnly();
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
                // Detect Order Total Price
                $this->detectOrderTotalPrice();
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
        // Manage Custom Carrier Codes
        if (CarrierCodes::isCustomCarrier($carrierCode)) {
            $carrierCode = $this->doCustomCarrierUpdates($carrierCode);
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

    /**
     * Fetch Order Total Price
     */
    private function detectOrderTotalPrice(): void
    {
        //====================================================================//
        // If NOT in Write Context
        if (!isset($this->in["total"])) {
            $this->totalPrice = null;

            return;
        }
        $this->totalPrice = (float) $this->in["total"];
        unset($this->in["total"]);
    }

    /**
     * Update Carrier Code thanks to Custom Configurations
     *
     * @param string $carrierName
     *
     * @return string
     */
    private function doCustomCarrierUpdates(string $carrierName): string
    {
        //====================================================================//
        // Safety Check - NO Order Total Price Given
        if (null === $this->totalPrice) {
            return $carrierName;
        }
        //====================================================================//
        // Apply Custom Rules
        switch ($carrierName) {
            //====================================================================//
            // VET - Colissimo, Signed above 49 €
            case "VET_COL":
                return ($this->totalPrice < 49) ? "COL_9L" : "COL_9V";
            //====================================================================//
            // VET - Colis Privé, Signed above 49 €
            case "VET_PRIV":
                return ($this->totalPrice < 49) ? "COLPRIV" : "COLPRIVAS";
        }

        return $carrierName;
    }
}
