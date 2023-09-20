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

namespace   Splash\Connectors\Optilog\Objects\Order;

use Splash\Connectors\Optilog\Models\CarrierCodes;
use Splash\Connectors\Optilog\Models\RestHelper as API;
use Splash\Core\SplashCore      as Splash;

/**
 * Optilog Orders Tracking Fields Access
 */
trait TrackingTrait
{
    /**
     * @var null|float
     */
    private ?float $totalPrice = null;

    /**
     * Build Fields using FieldFactory
     */
    protected function buildTrackingFields(): void
    {
        //====================================================================//
        // Order Shipping Method
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("Transporteur")
            ->name("Code Transporteur")
            ->description(
                "Les associations entre codes Code Optilog et "
                ."Codes Transporteur Splash sont disponible dans la configuration du Connecteur."
            )
            ->microData("http://schema.org/ParcelDelivery", "identifier")
            ->group("Tracking")
            ->addChoices($this->getUserCarrierNames())
            ->setPreferWrite()
            ->isWriteOnly(!API::isApiV2Mode())
        ;
        //====================================================================//
        // Order Shipping Method Name
        if (API::isApiV2Mode()) {
            $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->identifier("TransportID")
                ->name("Nom Transporteur")
                ->description("Nom du Transporteur pour cette commande")
                ->microData("http://schema.org/ParcelDelivery", "name")
                ->group("Tracking")
                ->isReadOnly()
            ;
        }
        //====================================================================//
        // Order Tracking Number
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("Bordereau")
            ->name("Tracking Number")
            ->microData("http://schema.org/ParcelDelivery", "trackingNumber")
            ->group("Tracking")
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Tracking Url
        $this->fieldsFactory()->create(SPL_T_URL)
            ->identifier("URL")
            ->name("Tracking Url")
            ->microData("http://schema.org/ParcelDelivery", "trackingUrl")
            ->group("Tracking")
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Total Price TTC
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->identifier("total")
            ->name("Total (Tax incl.)")
            ->microData("http://schema.org/Invoice", "totalPaymentDueTaxIncluded")
            ->isWriteOnly()
        ;
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    protected function getTrackingFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'Transporteur':
                $this->out[$fieldName] = $this->object->Transport->Code ?? null;

                break;
            case 'TransportID':
                $this->out[$fieldName] = $this->object->Transport->ID ?? null;

                break;
            case 'Bordereau':
            case 'URL':
                $this->getFirstParcelFromV2();
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
     * @param string      $fieldName Field Identifier / Name
     * @param null|string $fieldData Field Data
     */
    protected function setTrackingFields(string $fieldName, ?string $fieldData): void
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
     * Check if this Order is Allowed Writing on Optilog
     *
     * @return bool
     */
    protected function isAllowedCarrier(): bool
    {
        //====================================================================//
        // Check If Received Order Carrier Name is Given
        if (!isset($this->in["Transporteur"]) || empty($this->in["Transporteur"])
            || !is_scalar($this->in["Transporteur"])) {
            return true;
        }
        //====================================================================//
        // Detect Carrier Code
        $carrierCode = $this->getCarrierCode((string) $this->in["Transporteur"]);
        if ("REJECTED" != $carrierCode) {
            return true;
        }
        Splash::log()->war("Rejected Carrier Code Detected...");

        return false;
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
        if (is_array($carriers) && isset($carriers[trim($carrierName)])) {
            $carrierCode = $carriers[trim($carrierName)];
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
        if (!array_key_exists("total", (array) $this->in)) {
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
            // VET - Colissimo, Signed above 100 €
            case "VET_COL":
                return ($this->totalPrice < 100) ? "COL_9L" : "COL_9V";
                //====================================================================//
                // VET - Colis Privé, Signed above 85 €
            case "VET_PRIV":
                return ($this->totalPrice < 85) ? "COLPRIV" : "COLPRIVAS";
        }

        return $carrierName;
    }

    /**
     * API V2: Fetch Information from First Shipped Parcel
     *
     * @return void
     */
    private function getFirstParcelFromV2(): void
    {
        //====================================================================//
        // Safety Check - We are on API V2
        if (!API::isApiV2Mode()) {
            return;
        }
        //====================================================================//
        // Safety Check - First Parcel information available
        if (!isset($this->object->Colis->Parcels[0]) || !is_object($this->object->Colis->Parcels[0])) {
            $this->object->Bordereau = null;
            $this->object->Url = null;

            return;
        }
        $firstParcel = &$this->object->Colis->Parcels[0];

        /** @codingStandardsIgnoreStart @phpstan-ignore-next-line */
        $this->object->Bordereau = $firstParcel->Bordereau ?? "";
        /** @phpstan-ignore-next-line */
        $this->object->URL = $firstParcel->URL ?? "";
        /** @codingStandardsIgnoreEnd */
    }
}
