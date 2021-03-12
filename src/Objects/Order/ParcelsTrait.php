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

use Splash\Connectors\Optilog\Models\RestHelper as API;
use Splash\Connectors\Optilog\Models\StatusHelper;
use stdClass;

/**
 * Access to Order Parcels Details Fields
 */
trait ParcelsTrait
{
    /**
     * @var string
     */
    private static $parcelsList = "parcels";

    /**
     * Build Fields using FieldFactory
     */
    protected function buildParcelsFields(): void
    {
        //====================================================================//
        // Check if we are on API V2
        if (!API::isApiV2Mode()) {
            return;
        }

        //====================================================================//
        // PARCEL - Identifier
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("id")
            ->name("Identifier")
            ->inList(self::$parcelsList)
            ->microdata("https://schema.org/ParcelDelivery", "identifier")
            ->isReadOnly()
        ;

        //====================================================================//
        // PARCEL - Status
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("IdStatut")
            ->name("Status")
            ->inList(self::$parcelsList)
            ->microdata("https://schema.org/ParcelDelivery", "deliveryStatus")
            ->isReadOnly()
        ;

        //====================================================================//
        // PARCEL - Tracking Number
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("Bordereau")
            ->name("Tracking Number")
            ->inList(self::$parcelsList)
            ->microdata("https://schema.org/ParcelDelivery", "trackingNumber")
            ->isReadOnly()
        ;

        //====================================================================//
        // PARCEL - Tracking Url
        $this->fieldsFactory()->create(SPL_T_URL)
            ->identifier("URL")
            ->name("Tracking Url")
            ->inList(self::$parcelsList)
            ->microdata("https://schema.org/ParcelDelivery", "trackingUrl")
            ->isReadOnly()
        ;

        //====================================================================//
        // PARCEL - Weight
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->identifier("Poids")
            ->name("Weight (kg)")
            ->inList(self::$parcelsList)
            ->microdata("https://schema.org/ParcelDelivery", "weight")
            ->isReadOnly()
        ;

        //====================================================================//
        // PARCEL - Contents Lines Unique IDs
        $this->fieldsFactory()->create(SPL_T_INLINE)
            ->identifier("IDunique")
            ->name("Contents IDs")
            ->inList(self::$parcelsList)
            ->microdata("https://schema.org/ParcelDelivery", "itemShipped")
            ->isReadOnly()
        ;

        //====================================================================//
        // PARCEL - Contents Lines SKUs
        $this->fieldsFactory()->create(SPL_T_INLINE)
            ->identifier("ID")
            ->name("Contents SKUs")
            ->inList(self::$parcelsList)
            ->isReadOnly()
        ;

        //====================================================================//
        // PARCEL - Contents Lines Quantity
        $this->fieldsFactory()->create(SPL_T_INLINE)
            ->identifier("Servie")
            ->name("Qty")
            ->inList(self::$parcelsList)
            ->isReadOnly()
        ;

        //====================================================================//
        // PARCEL - Serial Shipping Container Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("SSCC")
            ->name("SSCC")
            ->description("Serial Shipping Container Code")
            ->inList(self::$parcelsList)
            ->microdata("https://schema.org/ParcelDelivery", "disambiguatingDescription")
            ->isReadOnly()
        ;
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    protected function getParcelsFields($key, $fieldName): void
    {
        //====================================================================//
        // Check if List field & Init List Array
        $fieldId = self::lists()->InitOutput($this->out, self::$parcelsList, $fieldName);
        if (!$fieldId) {
            return;
        }
        //====================================================================//
        // Verify Parcels List is Not Empty
        $parcels = array();
        if (isset($this->object->Colis->Parcels) && is_array($this->object->Colis->Parcels)) {
            $parcels = $this->object->Colis->Parcels;
        }
        //====================================================================//
        // Fill List with Data
        foreach ($parcels as $index => $parcel) {
            $value = $this->getParcelFieldData($parcel, (string) $index, $fieldId);
            if (null === $value) {
                return;
            }
            //====================================================================//
            // Insert Data in List
            self::lists()->Insert($this->out, self::$parcelsList, $fieldName, $index, $value);
        }

        unset($this->in[$key]);
    }

    /**
     * Read Order Line Item Field Data
     *
     * @param stdClass $itemData Parcel ItemData
     * @param string   $index    Parcel Index
     * @param string   $fieldId  Field Identifier / Name
     *
     * @return null|float|string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function getParcelFieldData(stdClass $itemData, string $index, string $fieldId)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldId) {
            //====================================================================//
            // Order Parcels Direct Reading Data
            case 'id':
                return self::buidParcelId($this->object->DestID, $itemData, $index);
            case 'IdStatut':
                return isset($itemData->IdStatut)
                    ? (string) StatusHelper::toSplash($itemData->IdStatut)
                    : "";
            case 'Poids':
                return isset($itemData->{$fieldId}) ? (float) $itemData->{$fieldId} : 0.0;
            case 'Bordereau':
            case 'URL':
            case 'SSCC':
                return isset($itemData->{$fieldId}) ? (string) $itemData->{$fieldId} : "";
            case 'IDunique':
            case 'ID':
            case 'Servie':
                return self::extractContentValue($itemData, $fieldId);
            default:
                return null;
        }
    }

    /**
     * Build Parcel Id Depending on Contents
     *
     * - IF only ONE Product, use IDunique as Parcel Name
     * - IF more than ONE, generate ID based on Order Id
     *
     * @param string   $orderId  OrderId | DestID
     * @param stdClass $itemData Optilog Parcel ItemData
     * @param string   $index    Parcel Index (zero based)
     *
     * @return string
     */
    private static function buidParcelId(string $orderId, stdClass $itemData, string $index): string
    {
        //====================================================================//
        // If Parcel has an Unique Content
        if (isset($itemData->Contenu) && is_array($itemData->Contenu) && (1 == count($itemData->Contenu))) {
            //====================================================================//
            // Content has an unique ID
            if (isset($itemData->Contenu[0]->IDunique) && !empty($itemData->Contenu[0]->IDunique)) {
                return $itemData->Contenu[0]->IDunique;
            }
        }

        return $orderId.".P.".$index;
    }

    /**
     * Extract Line Item Contents Data
     *
     * @param stdClass $itemData Optilog Parcel ItemData
     * @param string   $fieldId  Field Identifier / Name
     *
     * @return string
     */
    private static function extractContentValue(stdClass $itemData, string $fieldId): string
    {
        $values = array();

        if (isset($itemData->Contenu) && is_array($itemData->Contenu)) {
            foreach ($itemData->Contenu as $contenu) {
                $values[] = isset($contenu->{$fieldId}) ? (string) $contenu->{$fieldId} : "";
            }
        }

        return (string) json_encode($values);
    }
}
